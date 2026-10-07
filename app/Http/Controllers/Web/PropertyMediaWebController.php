<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyMedia;
use App\Jobs\ProcessPropertyMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PropertyMediaWebController extends Controller
{
    public function index(?Property $property = null)
    {
        if (!$property || !$property->exists) {
            $property = Property::with('media')->first();
            if (!$property) {
                // Create a placeholder property if database is empty so media manager can be tested
                $property = Property::create([
                    'property_code' => 'PROP-' . strtoupper(\Illuminate\Support\Str::random(6)),
                    'title' => 'Luxury Villa in Bole (Sample Property)',
                    'status' => 'draft',
                    'bedrooms' => 4,
                    'bathrooms' => 3,
                    'building_area' => 380,
                ]);
            }
        }

        $property->load(['media' => function ($q) {
            $q->orderBy('sort_order');
        }]);

        return view('dashboard.properties.media', compact('property'));
    }

    public function store(Request $request, Property $property)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,png,jpg,webp,mp4,mov,pdf|max:20480',
        ]);

        $file = $request->file('file');
        $mimeType = $file->getMimeType();
        
        $kind = 'image';
        if (str_starts_with($mimeType, 'video')) {
            $kind = 'video';
        } elseif ($mimeType === 'application/pdf') {
            $kind = 'document';
        }

        $path = $file->store("properties/{$property->id}", 'public');

        $width = null;
        $height = null;
        if ($kind === 'image') {
            $imageInfo = @getimagesize($file->getRealPath());
            if ($imageInfo) {
                $width = $imageInfo[0];
                $height = $imageInfo[1];
            }
        }

        $media = $property->media()->create([
            'kind' => $kind,
            'storage_key' => $path,
            'mime_type' => $mimeType,
            'width' => $width,
            'height' => $height,
            'checksum' => md5_file($file->getRealPath()),
            'status' => 'processed',
            'visibility' => 'public',
            'sort_order' => $property->media()->count(),
        ]);

        if (class_exists(ProcessPropertyMedia::class)) {
            try {
                ProcessPropertyMedia::dispatch($media);
            } catch (\Throwable $e) {
                // Queue fallback gracefully
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Media uploaded successfully.',
                'media' => $media,
            ], 201);
        }

        return redirect()->back()->with('success', 'Media file uploaded successfully.');
    }

    public function updateOrder(Request $request, Property $property)
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'required|string',
        ]);

        foreach ($validated['order'] as $index => $id) {
            $property->media()->where('id', $id)->update(['sort_order' => $index]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Media order updated successfully.',
            ]);
        }

        return redirect()->back()->with('success', 'Media order updated successfully.');
    }

    public function setPrimary(Property $property, PropertyMedia $media)
    {
        if ($media->property_id !== $property->id) {
            abort(404);
        }

        // Move target media to order 0, increment others
        $otherMedias = $property->media()->where('id', '!=', $media->id)->orderBy('sort_order')->get();
        
        $media->update(['sort_order' => 0]);

        $order = 1;
        foreach ($otherMedias as $item) {
            $item->update(['sort_order' => $order++]);
        }

        if (request()->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Primary cover photo updated.',
            ]);
        }

        return redirect()->back()->with('success', 'Cover photo set successfully.');
    }

    public function destroy(Property $property, PropertyMedia $media)
    {
        if ($media->property_id !== $property->id) {
            abort(404);
        }

        Storage::disk('public')->delete($media->storage_key);
        $media->delete();

        // Re-order remaining
        $remaining = $property->media()->orderBy('sort_order')->get();
        foreach ($remaining as $idx => $item) {
            $item->update(['sort_order' => $idx]);
        }

        if (request()->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Media deleted successfully.',
            ]);
        }

        return redirect()->back()->with('success', 'Media file deleted successfully.');
    }
}
