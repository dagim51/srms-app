<?php

namespace App\Domains\Property\Http\Controllers\Api\V1;

use App\Domains\Property\Http\Controllers\Controller;
use App\Jobs\ProcessPropertyMedia;
use App\Models\Property;
use App\Models\PropertyMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class PropertyMediaController extends Controller
{
    public function store(Request $request, Property $property)
    {
        Gate::authorize('update', $property);

        $request->validate([
            'file' => 'required|file|mimes:jpeg,png,jpg,webp,mp4,mov|max:20480', // 20MB limit
        ]);

        $file = $request->file('file');
        $mimeType = $file->getMimeType();
        $kind = str_starts_with($mimeType, 'video') ? 'video' : 'image';

        $path = $file->store("properties/{$property->id}", 'public');

        $media = $property->media()->create([
            'kind' => $kind,
            'storage_key' => $path,
            'mime_type' => $mimeType,
            'status' => 'pending',
            'sort_order' => $property->media()->count(),
        ]);

        ProcessPropertyMedia::dispatch($media);

        return response()->json($media, 201);
    }

    public function updateOrder(Request $request, Property $property)
    {
        Gate::authorize('update', $property);

        $validated = $request->validate([
            'media' => 'required|array',
            'media.*.id' => 'required|exists:property_media,id',
            'media.*.sort_order' => 'required|integer',
        ]);

        foreach ($validated['media'] as $item) {
            $property->media()->where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['message' => 'Order updated']);
    }

    public function destroy(Request $request, Property $property, PropertyMedia $media)
    {
        Gate::authorize('update', $property);

        if ($media->property_id !== $property->id) {
            abort(404);
        }

        Storage::disk('public')->delete($media->storage_key);
        $media->delete();

        return response()->json(['message' => 'Media deleted']);
    }
}
