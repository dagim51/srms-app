<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\AccommodationAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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

        return view('dashboard.accommodations.calendar', compact('accommodation', 'user', 'blocks', 'currentDate'));
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
            'type' => 'nullable|string|in:blocked,external_reservation'
        ]);

        // Check for overlapping blocks
        $overlap = AccommodationAvailability::where('accommodation_id', $accommodation->id)
            ->where(function($query) use ($request) {
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
}
