<?php

namespace App\Traits;

use App\Models\Role;
use App\Models\Permission;

trait HasRoles
{
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot('organization_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')
            ->withPivot('organization_id');
    }

    public function assignRole($role, $organizationId = null)
    {
        $roleId = $role instanceof Role ? $role->id : Role::where('name', $role)->firstOrFail()->id;

        if (!$this->hasRole($roleId, $organizationId)) {
            $this->roles()->attach($roleId, ['organization_id' => $organizationId]);
        }
    }

    public function hasRole($role, $organizationId = null)
    {
        $roleNameOrId = $role instanceof Role ? $role->id : $role;

        return $this->roles()
            ->where(function ($query) use ($roleNameOrId) {
                if (\Illuminate\Support\Str::isUuid($roleNameOrId)) {
                    $query->where('roles.id', $roleNameOrId)
                          ->orWhere('roles.name', $roleNameOrId);
                } else {
                    $query->where('roles.name', $roleNameOrId);
                }
            })
            ->wherePivot('organization_id', $organizationId)
            ->exists();
    }

    public function hasPermissionTo($permission, $organizationId = null)
    {
        $permissionCode = $permission instanceof Permission ? $permission->code : $permission;

        // Check direct permissions
        $hasDirect = $this->permissions()
            ->where('code', $permissionCode)
            ->wherePivot('organization_id', $organizationId)
            ->exists();

        if ($hasDirect) return true;

        // Check through roles
        return $this->roles()
            ->wherePivot('organization_id', $organizationId)
            ->whereHas('permissions', function ($query) use ($permissionCode) {
                $query->where('code', $permissionCode);
            })->exists();
    }
}
