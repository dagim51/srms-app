<?php

namespace App\Domains\Rental\Models;

use App\Domains\Identity\Models\User;
use App\Events\Rentals\LeaseActivated;
use App\Models\Property;
use App\Models\RentSchedule;
use App\Models\TenantProfile;
use App\Support\Traits\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $property_id
 * @property string $tenant_profile_id
 * @property string $start_date
 * @property string $end_date
 * @property float $rent_amount
 * @property float $deposit_amount
 * @property string $status
 * @property string|null $agreement_digest
 * @property Carbon|null $signed_by_tenant_at
 * @property Carbon|null $signed_by_landlord_at
 * @property array<string, mixed>|null $signatures
 * @property-read Property|null $property
 * @property-read TenantProfile|null $tenantProfile
 */
class Lease extends Model
{
    use Auditable, HasUuids;

    protected $fillable = [
        'property_id',
        'tenant_profile_id',
        'start_date',
        'end_date',
        'rent_amount',
        'deposit_amount',
        'status',
        'agreement_digest',
        'signed_by_tenant_at',
        'signed_by_landlord_at',
        'signatures',
    ];

    protected $casts = [
        'signed_by_tenant_at' => 'datetime',
        'signed_by_landlord_at' => 'datetime',
        'signatures' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'rent_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function tenantProfile(): BelongsTo
    {
        return $this->belongsTo(TenantProfile::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(RentSchedule::class);
    }

    public function isFullySigned(): bool
    {
        return ! is_null($this->signed_by_tenant_at) && ! is_null($this->signed_by_landlord_at);
    }

    public function sign(User $user, string $ipAddress, ?string $signatureData = null): bool
    {
        $this->loadMissing(['property', 'tenantProfile']);

        $isTenant = $this->tenantProfile && $this->tenantProfile->user_id === $user->id;
        $isLandlord = $this->property && $this->property->owner_user_id === $user->id;

        if (! $isTenant && ! $isLandlord) {
            throw new \DomainException('User is not authorized to sign this lease agreement.');
        }

        $signatures = $this->signatures ?? [];
        $now = Carbon::now();

        if ($isTenant) {
            $this->signed_by_tenant_at = $now;
            $signatures['tenant'] = [
                'user_id' => $user->id,
                'signed_at' => $now->toIso8601String(),
                'ip_address' => $ipAddress,
                'signature' => $signatureData ?? 'DIGITAL_SIGNATURE_VERIFIED',
            ];
        }

        if ($isLandlord) {
            $this->signed_by_landlord_at = $now;
            $signatures['landlord'] = [
                'user_id' => $user->id,
                'signed_at' => $now->toIso8601String(),
                'ip_address' => $ipAddress,
                'signature' => $signatureData ?? 'DIGITAL_SIGNATURE_VERIFIED',
            ];
        }

        $this->signatures = $signatures;
        $this->save();

        if ($this->isFullySigned() && $this->status !== 'active') {
            $this->activate();
        }

        return true;
    }

    public function generateSchedules(): int
    {
        if ($this->schedules()->count() > 0) {
            return 0;
        }

        $start = Carbon::parse($this->start_date);
        $end = Carbon::parse($this->end_date);
        $current = $start->copy();
        $count = 0;

        while ($current->lte($end)) {
            RentSchedule::create([
                'lease_id' => $this->id,
                'due_date' => $current->toDateString(),
                'amount' => $this->rent_amount,
                'status' => 'unpaid',
            ]);
            $count++;
            $current->addMonth();
        }

        return $count;
    }

    public function activate(): void
    {
        $this->update(['status' => 'active']);
        $this->generateSchedules();

        event(new LeaseActivated($this));
    }
}
