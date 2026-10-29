<?php

namespace App\Observers;

use App\Domains\Administration\Models\AuditLog;
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
            $oldPrice = $property->getOriginal('price');
            $newPrice = $property->price;
            $currency = $property->currency ?? 'ETB';
            $oldCurrency = $property->getOriginal('currency') ?? $currency;

            PropertyPriceHistory::create([
                'property_id' => $property->id,
                'price' => $newPrice,
                'currency' => $currency,
                'changed_by' => auth()->id(),
                'reason' => request()->input('price_change_reason', 'Price updated'),
                'created_at' => now(),
            ]);

            AuditLog::log(
                event: 'price_changed',
                auditable: $property,
                newValues: ['price' => $newPrice, 'currency' => $currency],
                oldValues: ['price' => $oldPrice, 'currency' => $oldCurrency],
                notes: "Property '{$property->title}' price changed from ".number_format((float) ($oldPrice ?? 0))." {$oldCurrency} to ".number_format((float) $newPrice)." {$currency}",
                actorId: auth()->id()
            );
        }

        if ($property->isDirty('status') && $property->status !== null) {
            $oldStatus = $property->getOriginal('status');
            $newStatus = $property->status;

            PropertyStatusHistory::create([
                'property_id' => $property->id,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'changed_by' => auth()->id(),
                'comment' => request()->input('status_change_comment', 'Status updated'),
                'created_at' => now(),
            ]);

            AuditLog::log(
                event: 'status_changed',
                auditable: $property,
                newValues: ['status' => $newStatus],
                oldValues: ['status' => $oldStatus],
                notes: "Property '{$property->title}' status changed from '{$oldStatus}' to '{$newStatus}'",
                actorId: auth()->id()
            );
        }
    }
}
