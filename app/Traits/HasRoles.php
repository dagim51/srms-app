<?php

namespace App\Traits;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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

    public function assignRole($role, $organizationId = null): void
    {
        $roleId = $role instanceof Role ? $role->id : Role::where('name', $role)->firstOrFail()->id;

        if (!$this->hasExactRole($roleId, $organizationId)) {
            $this->roles()->attach($roleId, ['organization_id' => $organizationId]);
        }
    }

    public function removeRole($role, $organizationId = null): void
    {
        $roleId = $role instanceof Role ? $role->id : Role::where('name', $role)->value('id');

        if ($roleId) {
            DB::table('user_roles')
                ->where('user_id', $this->id)
                ->where('role_id', $roleId)
                ->when(
                    $organizationId !== null,
                    fn($q) => $q->where('organization_id', $organizationId),
                    fn($q) => $q->whereNull('organization_id')
                )
                ->delete();
        }
    }

    public function syncRoles(array $roles, $organizationId = null): void
    {
        DB::table('user_roles')
            ->where('user_id', $this->id)
            ->when(
                $organizationId !== null,
                fn($q) => $q->where('organization_id', $organizationId),
                fn($q) => $q->whereNull('organization_id')
            )
            ->delete();

        foreach ($roles as $role) {
            $this->assignRole($role, $organizationId);
        }
    }

    /**
     * Check if user has exact role assignment for the given organization scope.
     */
    public function hasExactRole($role, $organizationId = null): bool
    {
        $roleNameOrId = $role instanceof Role ? $role->id : $role;

        return $this->roles()
            ->where(function ($query) use ($roleNameOrId) {
                if (Str::isUuid($roleNameOrId)) {
                    $query->where('roles.id', $roleNameOrId)
                          ->orWhere('roles.name', $roleNameOrId);
                } else {
                    $query->where('roles.name', $roleNameOrId);
                }
            })
            ->where(function ($query) use ($organizationId) {
                if ($organizationId !== null) {
                    $query->where('user_roles.organization_id', $organizationId);
                } else {
                    $query->whereNull('user_roles.organization_id');
                }
            })
            ->exists();
    }

    /**
     * Check if user has role. If $organizationId is provided, either an organization-scoped
     * assignment OR a global platform assignment (organization_id IS NULL) satisfies it.
     */
    public function hasRole($role, $organizationId = null): bool
    {
        $roleNameOrId = $role instanceof Role ? $role->id : $role;

        return $this->roles()
            ->where(function ($query) use ($roleNameOrId) {
                if (Str::isUuid($roleNameOrId)) {
                    $query->where('roles.id', $roleNameOrId)
                          ->orWhere('roles.name', $roleNameOrId);
                } else {
                    $query->where('roles.name', $roleNameOrId);
                }
            })
            ->where(function ($query) use ($organizationId) {
                if ($organizationId !== null) {
                    $query->where('user_roles.organization_id', $organizationId)
                          ->orWhereNull('user_roles.organization_id');
                } else {
                    $query->whereNull('user_roles.organization_id');
                }
            })
            ->exists();
    }

    /**
     * Check if user has any of the given roles.
     * $roles can be array or pipe/comma separated string: 'Administrator|Property Manager'
     */
    public function hasAnyRole($roles, $organizationId = null): bool
    {
        if (is_string($roles)) {
            $roles = preg_split('/[,|]/', $roles);
        }

        foreach ((array) $roles as $role) {
            $role = trim($role);
            if (!empty($role) && $this->hasRole($role, $organizationId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all of the given roles.
     */
    public function hasAllRoles($roles, $organizationId = null): bool
    {
        if (is_string($roles)) {
            $roles = preg_split('/[,|]/', $roles);
        }

        foreach ((array) $roles as $role) {
            $role = trim($role);
            if (!empty($role) && !$this->hasRole($role, $organizationId)) {
                return false;
            }
        }

        return true;
    }

    public function givePermissionTo($permission, $organizationId = null): void
    {
        $permissionId = $permission instanceof Permission
            ? $permission->id
            : Permission::where('code', $permission)->firstOrFail()->id;

        $hasExact = $this->permissions()
            ->where('permissions.id', $permissionId)
            ->where(function ($q) use ($organizationId) {
                if ($organizationId !== null) {
                    $q->where('user_permissions.organization_id', $organizationId);
                } else {
                    $q->whereNull('user_permissions.organization_id');
                }
            })->exists();

        if (!$hasExact) {
            $this->permissions()->attach($permissionId, ['organization_id' => $organizationId]);
        }
    }

    public function revokePermissionTo($permission, $organizationId = null): void
    {
        $permissionId = $permission instanceof Permission
            ? $permission->id
            : Permission::where('code', $permission)->value('id');

        if ($permissionId) {
            DB::table('user_permissions')
                ->where('user_id', $this->id)
                ->where('permission_id', $permissionId)
                ->when(
                    $organizationId !== null,
                    fn($q) => $q->where('organization_id', $organizationId),
                    fn($q) => $q->whereNull('organization_id')
                )
                ->delete();
        }
    }

    public function syncPermissions(array $permissions, $organizationId = null): void
    {
        DB::table('user_permissions')
            ->where('user_id', $this->id)
            ->when(
                $organizationId !== null,
                fn($q) => $q->where('organization_id', $organizationId),
                fn($q) => $q->whereNull('organization_id')
            )
            ->delete();

        foreach ($permissions as $permission) {
            $this->givePermissionTo($permission, $organizationId);
        }
    }

    public function hasPermissionTo($permission, $organizationId = null): bool
    {
        // Platform Administrator has super-admin rights across all permissions
        if ($this->hasRole('Administrator', $organizationId)) {
            return true;
        }

        $permissionCode = $permission instanceof Permission ? $permission->code : $permission;

        // Check direct permissions (scoped or global)
        $hasDirect = $this->permissions()
            ->where('code', $permissionCode)
            ->where(function ($query) use ($organizationId) {
                if ($organizationId !== null) {
                    $query->where('user_permissions.organization_id', $organizationId)
                          ->orWhereNull('user_permissions.organization_id');
                } else {
                    $query->whereNull('user_permissions.organization_id');
                }
            })
            ->exists();

        if ($hasDirect) return true;

        // Check through assigned roles (scoped or global)
        return $this->roles()
            ->where(function ($query) use ($organizationId) {
                if ($organizationId !== null) {
                    $query->where('user_roles.organization_id', $organizationId)
                          ->orWhereNull('user_roles.organization_id');
                } else {
                    $query->whereNull('user_roles.organization_id');
                }
            })
            ->whereHas('permissions', function ($query) use ($permissionCode) {
                $query->where('code', $permissionCode);
            })->exists();
    }

    public function hasAnyPermission($permissions, $organizationId = null): bool
    {
        if (is_string($permissions)) {
            $permissions = preg_split('/[,|]/', $permissions);
        }

        foreach ((array) $permissions as $perm) {
            $perm = trim($perm);
            if (!empty($perm) && $this->hasPermissionTo($perm, $organizationId)) {
                return true;
            }
        }

        return false;
    }

    public function getRoleNames($organizationId = null): Collection
    {
        return $this->roles()
            ->when($organizationId !== null, function ($query) use ($organizationId) {
                $query->where('user_roles.organization_id', $organizationId)
                      ->orWhereNull('user_roles.organization_id');
            })
            ->when($organizationId === null, fn($q) => $q->whereNull('user_roles.organization_id'))
            ->pluck('name');
    }

    public function getAllPermissions($organizationId = null): Collection
    {
        $direct = $this->permissions()
            ->when($organizationId !== null, function ($query) use ($organizationId) {
                $query->where('user_permissions.organization_id', $organizationId)
                      ->orWhereNull('user_permissions.organization_id');
            })
            ->when($organizationId === null, fn($q) => $q->whereNull('user_permissions.organization_id'))
            ->pluck('code');

        $viaRoles = Permission::whereHas('roles', function ($query) use ($organizationId) {
            $query->whereIn('roles.id', function ($sub) use ($organizationId) {
                $sub->select('role_id')
                    ->from('user_roles')
                    ->where('user_id', $this->id)
                    ->when($organizationId !== null, function ($q) use ($organizationId) {
                        $q->where('organization_id', $organizationId)
                          ->orWhereNull('organization_id');
                    })
                    ->when($organizationId === null, fn($q) => $q->whereNull('organization_id'));
            });
        })->pluck('code');

        return $direct->merge($viaRoles)->unique()->values();
    }
}
