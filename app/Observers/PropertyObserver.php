<?php

namespace App\Observers;

use App\Models\Property;
use App\Models\PropertyPriceHistory;
use App\Models\PropertyStatusHistory;

class PropertyObserver
{
    /**
     * Handle the Property "created" event.
     */
    public function created(Property $property): void
    {
        if ($property->price !== null) {
            PropertyPriceHistory::create([
                'property_id' => $property->id,
                'price' => $property->price,
                'currency' => $property->currency ?? 'ETB',
                'changed_by' => auth()->id() ?? $property->owner_user_id,
                'reason' => 'Initial property price',
                'created_at' => now()->subSecond(),
            ]);
        }

        if ($property->status !== null) {
            PropertyStatusHistory::create([
                'property_id' => $property->id,
                'from_status' => null,
                'to_status' => $property->status,
                'changed_by' => auth()->id() ?? $property->owner_user_id,
                'comment' => 'Initial property status',
                'created_at' => now()->subSecond(),
            ]);
        }
    }

    /**
     * Handle the Property "updating" event.
     */
    public function updating(Property $property): void
    {
        if ($property->isDirty('price') && $property->price !== null) {
            PropertyPriceHistory::create([
                'property_id' => $property->id,
                'price' => $property->price,
                'currency' => $property->currency ?? 'ETB',
                'changed_by' => auth()->id(),
                'reason' => request()->input('price_change_reason', 'Price updated'),
                'created_at' => now(),
            ]);
        }

        if ($property->isDirty('status') && $property->status !== null) {
            PropertyStatusHistory::create([
                'property_id' => $property->id,
                'from_status' => $property->getOriginal('status'),
                'to_status' => $property->status,
                'changed_by' => auth()->id(),
                'comment' => request()->input('status_change_comment', 'Status updated'),
                'created_at' => now(),
            ]);
        }
    }
}
