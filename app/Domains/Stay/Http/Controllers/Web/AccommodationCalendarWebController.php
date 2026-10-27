<?php

namespace App\Domains\Stay\Http\Controllers\Web;

use App\Domains\Stay\Services\CalendarSyncService;
use App\Domains\Stay\Services\ICalGeneratorService;
use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\AccommodationAvailability;
use App\Models\ExternalCalendarSync;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccommodationCalendarWebController extends Controller
{
    protected function getActiveUser()
    {
        return Auth::user();
    }

    public function show(Accommodation $accommodation, Request $request)
    {
        $user = $this->getActiveUser();

        if ($accommodation->host_id !== $user->id) {
            abort(403);
        }

        $accommodation->load('property');

        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        $currentDate = Carbon::createFromDate($year, $month, 1);
        $startDate = $currentDate->copy()->startOfMonth();
        $endDate = $currentDate->copy()->endOfMonth();

        // Get availability records covering this month
        $blocks = AccommodationAvailability::where('accommodation_id', $accommodation->id)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->get();

        $calendarSyncs = ExternalCalendarSync::where('accommodation_id', $accommodation->id)->latest()->get();
        $exportUrl = route('accommodations.calendar.feed', [
            'accommodation' => $accommodation,
            'token' => $accommodation->ical_token,
        ]);

        return view('dashboard.accommodations.calendar', compact('accommodation', 'user', 'blocks', 'currentDate', 'calendarSyncs', 'exportUrl'));
    }

    public function exportFeed(Request $request, Accommodation $accommodation)
    {
        $expectedToken = $accommodation->ical_token;

        if ($request->input('token') !== $expectedToken) {
            abort(403, 'Invalid or expired calendar feed subscription token.');
        }

        $ics = app(ICalGeneratorService::class)->generate($accommodation);

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="listing-' . $accommodation->id . '.ics"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    public function block(Request $request, Accommodation $accommodation)
    {
        $user = $this->getActiveUser();

        if ($accommodation->host_id !== $user->id) {
            abort(403);
        }

        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:255',
            'type' => 'nullable|string|in:blocked,external_reservation',
        ]);

        // Check for overlapping blocks
        $overlap = AccommodationAvailability::where('accommodation_id', $accommodation->id)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                    ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->end_date);
                    });
            })->exists();

        if ($overlap) {
            return back()->withErrors(['start_date' => 'These dates overlap with an existing block or reservation.']);
        }

        AccommodationAvailability::create([
            'accommodation_id' => $accommodation->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'type' => $request->type ?? 'blocked',
            'reason' => $request->reason,
        ]);

        return back()->with('success', 'Dates blocked successfully.');
    }

    public function unblock(Request $request, Accommodation $accommodation, AccommodationAvailability $availability)
    {
        $user = $this->getActiveUser();

        if ($accommodation->host_id !== $user->id || $availability->accommodation_id !== $accommodation->id) {
            abort(403);
        }

        $availability->delete();

        return back()->with('success', 'Dates unblocked successfully.');
    }

    public function storeSync(Request $request, Accommodation $accommodation, CalendarSyncService $syncService)
    {
        $user = $this->getActiveUser();

        if ($accommodation->host_id !== $user->id) {
            abort(403);
        }

        if (!$request->filled('url') && $request->filled('feed_url')) {
            $request->merge(['url' => $request->input('feed_url')]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'url' => 'required|string|max:1000',
            'sync_interval_minutes' => 'nullable|integer|min:15|max:1440',
        ]);

        $url = trim($validated['url']);
        if (!filter_var($url, FILTER_VALIDATE_URL) && !str_starts_with($url, 'webcal://')) {
            return back()->withErrors(['url' => 'Please provide a valid webcal or https calendar feed URL.']);
        }

        $sync = ExternalCalendarSync::create([
            'accommodation_id' => $accommodation->id,
            'name' => $validated['name'],
            'url' => $url,
            'sync_interval_minutes' => $validated['sync_interval_minutes'] ?? 60,
            'status' => 'active',
        ]);

        try {
            $count = $syncService->syncFeed($sync);
            $msg = "Connected '{$sync->name}' successfully. Imported {$count} external reservation(s).";
        } catch (\Exception $e) {
            $msg = "Connected '{$sync->name}', but initial sync warning: " . $e->getMessage();
        }

        return back()->with('success', $msg);
    }

    public function refreshSync(Request $request, Accommodation $accommodation, ExternalCalendarSync $sync, CalendarSyncService $syncService)
    {
        $user = $this->getActiveUser();

        if ($accommodation->host_id !== $user->id || $sync->accommodation_id !== $accommodation->id) {
            abort(403);
        }

        try {
            $count = $syncService->syncFeed($sync);
            return back()->with('success', "Feed '{$sync->name}' synchronized successfully ({$count} reservations updated).");
        } catch (\Exception $e) {
            return back()->withErrors(['sync' => "Sync error: " . $e->getMessage()]);
        }
    }

    public function destroySync(Request $request, Accommodation $accommodation, ExternalCalendarSync $sync)
    {
        $user = $this->getActiveUser();

        if ($accommodation->host_id !== $user->id || $sync->accommodation_id !== $accommodation->id) {
            abort(403);
        }

        // Delete all reservations imported from this sync
        AccommodationAvailability::where('accommodation_id', $accommodation->id)
            ->where('reason', 'like', "%[Sync:{$sync->name} #{$sync->id}]%")
            ->delete();

        $sync->delete();

        return back()->with('success', "External calendar feed '{$sync->name}' disconnected and imported reservations cleared.");
    }
}
