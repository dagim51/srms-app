<?php

namespace App\Domains\Identity\Models;

use App\Domains\Booking\Models\AccommodationBooking;
use App\Domains\CRM\Models\Lead;
use App\Domains\Organization\Models\OrganizationMember;
use App\Models\HandoverRecord;
use App\Support\Traits\Auditable;
use App\Traits\HasRoles;
use Database\Factories\Domains\Identity\Models\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'locale', 'phone', 'status', 'timezone', 'intent'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use Auditable, HasFactory, HasRoles, HasUuids, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'locale',
        'phone',
        'status',
        'timezone',
        'intent',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function apiTokens(): HasMany
    {
        return $this->hasMany(ApiToken::class);
    }

    public function handoverRecords(): HasMany
    {
        return $this->hasMany(HandoverRecord::class, 'buyer_id');
    }

    public function assignedLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    public function organizationMemberships(): HasMany
    {
        return $this->hasMany(OrganizationMember::class, 'user_id');
    }

    public function accommodationBookings(): HasMany
    {
        return $this->hasMany(AccommodationBooking::class, 'user_id');
    }
}
