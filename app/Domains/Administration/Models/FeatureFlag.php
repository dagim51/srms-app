<?php

namespace App\Domains\Administration\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    use HasUuids;

    protected $table = 'feature_flags';

    protected $fillable = [
        'name',
        'is_enabled',
        'allowed_roles',
        'description',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'allowed_roles' => 'array',
    ];

    /**
     * Determine if a feature is enabled for a given role.
     */
    public static function isEnabled(string $name, ?string $role = null): bool
    {
        $flag = static::where('name', $name)->first();
        if (! $flag) {
            return false;
        }

        if (! $flag->is_enabled) {
            return false;
        }

        if (empty($flag->allowed_roles)) {
            return true;
        }

        return $role !== null && in_array($role, $flag->allowed_roles);
    }
}
