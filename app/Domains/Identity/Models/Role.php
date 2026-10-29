<?php

namespace App\Domains\Identity\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property string $id
 * @property string $name
 * @property string $guard_name
 * @property string $status
 */
class Role extends Model
{
    use HasUuids;

    protected $fillable = ['name', 'guard_name', 'status', 'description', 'is_system'];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    /**
     * @return BelongsToMany<Permission, $this>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles')
            ->withPivot('organization_id');
    }

    public function hasPermissionTo(string $permission): bool
    {
        return $this->permissions()->where('code', $permission)->exists();
    }

    public function isSystem(): bool
    {
        return (bool) ($this->is_system || in_array($this->name, self::systemRoleNames()));
    }

    public function canBeDeleted(): bool
    {
        return ! $this->isSystem() && $this->users()->count() === 0;
    }

    public static function systemRoleNames(): array
    {
        return [
            'Administrator',
            'Buyer',
            'Tenant',
            'Guest / Traveler',
            'Guest',
            'Property Owner',
            'Host',
            'Landlord',
            'Agent',
            'Real Estate Company',
            'Developer',
            'Property Manager',
            'Professional Service Provider',
            'Moderator',
            'Verification Officer',
            'Finance Officer',
            'Customer Support',
            'User',
            'Seller',
        ];
    }
}
