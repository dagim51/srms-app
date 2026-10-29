<?php

namespace App\Traits;

use App\Domains\Administration\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait HasRoles
{
    /**
     * @return BelongsToMany<Role, $this>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot('organization_id');
    }

    /**
     * @return BelongsToMany<Permission, $this>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')
            ->withPivot('organization_id');
    }

    /**
     * Resolve hierarchical scopes for Company -> Branch -> Team inheritance (SRS Section 197).
     *
     * @return array{organization_id: string|null, branch_id: string|null, team_id: string|null}
     */
    public function resolveScopeHierarchy(?string $scopeId): array
    {
        if ($scopeId === null) {
            return ['organization_id' => null, 'branch_id' => null, 'team_id' => null];
        }

        // 1. Is it a Team?
        $team = DB::table('teams')->where('id', $scopeId)->first();
        if ($team && $team->branch_id) {
            $branch = DB::table('branches')->where('id', $team->branch_id)->first();

            return [
                'organization_id' => $branch?->organization_id,
                'branch_id' => $team->branch_id,
                'team_id' => $team->id,
            ];
        }

        // 2. Is it a Branch?
        $branch = DB::table('branches')->where('id', $scopeId)->first();
        if ($branch && $branch->organization_id) {
            return [
                'organization_id' => $branch->organization_id,
                'branch_id' => $branch->id,
                'team_id' => null,
            ];
        }

        // 3. Otherwise assume it is a root Organization
        return [
            'organization_id' => $scopeId,
            'branch_id' => null,
            'team_id' => null,
        ];
    }

    public function assignRole($role, $organizationId = null): void
    {
        $roleModel = $role instanceof Role ? $role : Role::where('name', $role)->firstOrFail();
        $roleId = $roleModel->id;

        $resolved = $this->resolveScopeHierarchy($organizationId);

        if (! $this->hasExactRole($roleId, $organizationId)) {
            $oldRoles = $this->getRoleNames($organizationId)->toArray();

            $pivotData = ['organization_id' => $resolved['organization_id']];
            if (Schema::hasColumn('user_roles', 'branch_id')) {
                $pivotData['branch_id'] = $resolved['branch_id'];
            }
            if (Schema::hasColumn('user_roles', 'team_id')) {
                $pivotData['team_id'] = $resolved['team_id'];
            }

            $this->roles()->attach($roleId, $pivotData);

            $newRoles = $this->getRoleNames($organizationId)->toArray();

            // Security Audit Trail with Old vs New Diffing (SRS Section 142)
            AuditLog::log(
                event: 'role_assigned',
                auditable: $this,
                newValues: ['roles' => $newRoles, 'assigned_role' => $roleModel->name, 'scope' => $resolved],
                oldValues: ['roles' => $oldRoles],
                notes: "Assigned role '{$roleModel->name}' to user '{$this->name}'".($organizationId ? " for scope [{$organizationId}]" : ' (global)'),
                actorId: auth()->id()
            );
        }
    }

    public function removeRole($role, $organizationId = null): void
    {
        $roleModel = $role instanceof Role ? $role : Role::where('name', $role)->first();
        $roleId = $roleModel?->id;

        if ($roleId) {
            $oldRoles = $this->getRoleNames($organizationId)->toArray();
            $resolved = $this->resolveScopeHierarchy($organizationId);

            DB::table('user_roles')
                ->where('user_id', $this->id)
                ->where('role_id', $roleId)
                ->when(
                    $organizationId !== null,
                    function ($q) use ($resolved) {
                        $q->where('organization_id', $resolved['organization_id']);
                        if (Schema::hasColumn('user_roles', 'branch_id')) {
                            if ($resolved['branch_id'] !== null) {
                                $q->where('branch_id', $resolved['branch_id']);
                            } else {
                                $q->whereNull('branch_id');
                            }
                        }
                        if (Schema::hasColumn('user_roles', 'team_id')) {
                            if ($resolved['team_id'] !== null) {
                                $q->where('team_id', $resolved['team_id']);
                            } else {
                                $q->whereNull('team_id');
                            }
                        }
                    },
                    fn ($q) => $q->whereNull('organization_id')
                )
                ->delete();

            $newRoles = $this->getRoleNames($organizationId)->toArray();

            // Security Audit Trail with Old vs New Diffing (SRS Section 142)
            AuditLog::log(
                event: 'role_revoked',
                auditable: $this,
                newValues: ['roles' => $newRoles, 'revoked_role' => $roleModel->name, 'scope' => $resolved],
                oldValues: ['roles' => $oldRoles],
                notes: "Revoked role '{$roleModel->name}' from user '{$this->name}'".($organizationId ? " for scope [{$organizationId}]" : ' (global)'),
                actorId: auth()->id()
            );
        }
    }

    public function syncRoles(array $roles, $organizationId = null): void
    {
        $oldRoles = $this->getRoleNames($organizationId)->toArray();
        $resolved = $this->resolveScopeHierarchy($organizationId);

        DB::table('user_roles')
            ->where('user_id', $this->id)
            ->when(
                $organizationId !== null,
                function ($q) use ($resolved) {
                    $q->where('organization_id', $resolved['organization_id']);
                    if (Schema::hasColumn('user_roles', 'branch_id')) {
                        if ($resolved['branch_id'] !== null) {
                            $q->where('branch_id', $resolved['branch_id']);
                        } else {
                            $q->whereNull('branch_id');
                        }
                    }
                    if (Schema::hasColumn('user_roles', 'team_id')) {
                        if ($resolved['team_id'] !== null) {
                            $q->where('team_id', $resolved['team_id']);
                        } else {
                            $q->whereNull('team_id');
                        }
                    }
                },
                fn ($q) => $q->whereNull('organization_id')
            )
            ->delete();

        $processedIds = [];
        foreach ($roles as $role) {
            $roleId = $role instanceof Role ? $role->id : Role::where('name', $role)->firstOrFail()->id;
            if (in_array($roleId, $processedIds, true)) {
                continue;
            }
            $processedIds[] = $roleId;

            $pivotData = ['organization_id' => $resolved['organization_id']];
            if (Schema::hasColumn('user_roles', 'branch_id')) {
                $pivotData['branch_id'] = $resolved['branch_id'];
            }
            if (Schema::hasColumn('user_roles', 'team_id')) {
                $pivotData['team_id'] = $resolved['team_id'];
            }
            $this->roles()->attach($roleId, $pivotData);
        }

        $newRoles = $this->getRoleNames($organizationId)->toArray();

        // Security Audit Trail with Old vs New Diffing (SRS Section 142)
        AuditLog::log(
            event: 'roles_synced',
            auditable: $this,
            newValues: ['roles' => $newRoles, 'scope' => $resolved],
            oldValues: ['roles' => $oldRoles],
            notes: "Synced roles for user '{$this->name}' from [".implode(', ', $oldRoles).'] to ['.implode(', ', $newRoles).']',
            actorId: auth()->id()
        );
    }

    /**
     * Check if user has exact role assignment for the given organization scope (no inheritance).
     */
    public function hasExactRole($role, $organizationId = null): bool
    {
        $roleNameOrId = $role instanceof Role ? $role->id : $role;
        $resolved = $this->resolveScopeHierarchy($organizationId);

        return $this->roles()
            ->where(function ($query) use ($roleNameOrId) {
                if (Str::isUuid($roleNameOrId)) {
                    $query->where('roles.id', $roleNameOrId)
                        ->orWhere('roles.name', $roleNameOrId);
                } else {
                    $query->where('roles.name', $roleNameOrId);
                }
            })
            ->where(function ($query) use ($resolved, $organizationId) {
                if ($organizationId !== null) {
                    $query->where('user_roles.organization_id', $resolved['organization_id']);
                    if (Schema::hasColumn('user_roles', 'branch_id')) {
                        if ($resolved['branch_id'] !== null) {
                            $query->where('user_roles.branch_id', $resolved['branch_id']);
                        } else {
                            $query->whereNull('user_roles.branch_id');
                        }
                    }
                    if (Schema::hasColumn('user_roles', 'team_id')) {
                        if ($resolved['team_id'] !== null) {
                            $query->where('user_roles.team_id', $resolved['team_id']);
                        } else {
                            $query->whereNull('user_roles.team_id');
                        }
                    }
                } else {
                    $query->whereNull('user_roles.organization_id');
                }
            })
            ->exists();
    }

    /**
     * Check if user has role. Supports hierarchical organization inheritance (Company -> Branch -> Team)
     * and global platform assignments (organization_id IS NULL).
     */
    public function hasRole($role, $organizationId = null): bool
    {
        $roleNameOrId = $role instanceof Role ? $role->id : $role;
        $resolved = $this->resolveScopeHierarchy($organizationId);

        return $this->roles()
            ->where(function ($query) use ($roleNameOrId) {
                if (Str::isUuid($roleNameOrId)) {
                    $query->where('roles.id', $roleNameOrId)
                        ->orWhere('roles.name', $roleNameOrId);
                } else {
                    $query->where('roles.name', $roleNameOrId);
                }
            })
            ->where(function ($query) use ($resolved, $organizationId) {
                if ($organizationId !== null) {
                    $query->where(function ($sub) use ($resolved) {
                        // 1. Global platform role
                        $sub->whereNull('user_roles.organization_id');

                        // 2. Organization-level role (inherits down to all branches and teams)
                        if ($resolved['organization_id']) {
                            $sub->orWhere(function ($qOrg) use ($resolved) {
                                $qOrg->where('user_roles.organization_id', $resolved['organization_id']);
                                if (Schema::hasColumn('user_roles', 'branch_id')) {
                                    $qOrg->whereNull('user_roles.branch_id');
                                }
                            });
                        }

                        // 3. Branch-level role (inherits down to all teams in this branch)
                        if ($resolved['branch_id'] && Schema::hasColumn('user_roles', 'branch_id')) {
                            $sub->orWhere(function ($qBranch) use ($resolved) {
                                $qBranch->where('user_roles.branch_id', $resolved['branch_id']);
                                if (Schema::hasColumn('user_roles', 'team_id')) {
                                    $qBranch->whereNull('user_roles.team_id');
                                }
                            });
                        }

                        // 4. Team-level role (exact team match)
                        if ($resolved['team_id'] && Schema::hasColumn('user_roles', 'team_id')) {
                            $sub->orWhere('user_roles.team_id', $resolved['team_id']);
                        }
                    });
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
            if (! empty($role) && $this->hasRole($role, $organizationId)) {
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
            if (! empty($role) && ! $this->hasRole($role, $organizationId)) {
                return false;
            }
        }

        return true;
    }

    public function givePermissionTo($permission, $organizationId = null): void
    {
        $permModel = $permission instanceof Permission
            ? $permission
            : Permission::where('code', $permission)->firstOrFail();
        $permissionId = $permModel->id;
        $resolved = $this->resolveScopeHierarchy($organizationId);

        $hasExact = $this->permissions()
            ->where('permissions.id', $permissionId)
            ->where(function ($q) use ($resolved, $organizationId) {
                if ($organizationId !== null) {
                    $q->where('user_permissions.organization_id', $resolved['organization_id']);
                    if (Schema::hasColumn('user_permissions', 'branch_id')) {
                        if ($resolved['branch_id'] !== null) {
                            $q->where('user_permissions.branch_id', $resolved['branch_id']);
                        } else {
                            $q->whereNull('user_permissions.branch_id');
                        }
                    }
                    if (Schema::hasColumn('user_permissions', 'team_id')) {
                        if ($resolved['team_id'] !== null) {
                            $q->where('user_permissions.team_id', $resolved['team_id']);
                        } else {
                            $q->whereNull('user_permissions.team_id');
                        }
                    }
                } else {
                    $q->whereNull('user_permissions.organization_id');
                }
            })->exists();

        if (! $hasExact) {
            $oldPerms = $this->getAllPermissions($organizationId)->toArray();

            $pivotData = ['organization_id' => $resolved['organization_id']];
            if (Schema::hasColumn('user_permissions', 'branch_id')) {
                $pivotData['branch_id'] = $resolved['branch_id'];
            }
            if (Schema::hasColumn('user_permissions', 'team_id')) {
                $pivotData['team_id'] = $resolved['team_id'];
            }

            $this->permissions()->attach($permissionId, $pivotData);

            $newPerms = $this->getAllPermissions($organizationId)->toArray();

            // Security Audit Trail (SRS Section 142)
            AuditLog::log(
                event: 'permission_granted',
                auditable: $this,
                newValues: ['permissions' => $newPerms, 'granted_permission' => $permModel->code, 'scope' => $resolved],
                oldValues: ['permissions' => $oldPerms],
                notes: "Granted direct permission '{$permModel->code}' to user '{$this->name}'".($organizationId ? " for scope [{$organizationId}]" : ' (global)'),
                actorId: auth()->id()
            );
        }
    }

    public function revokePermissionTo($permission, $organizationId = null): void
    {
        $permModel = $permission instanceof Permission
            ? $permission
            : Permission::where('code', $permission)->first();
        $permissionId = $permModel?->id;

        if ($permissionId) {
            $oldPerms = $this->getAllPermissions($organizationId)->toArray();
            $resolved = $this->resolveScopeHierarchy($organizationId);

            DB::table('user_permissions')
                ->where('user_id', $this->id)
                ->where('permission_id', $permissionId)
                ->when(
                    $organizationId !== null,
                    function ($q) use ($resolved) {
                        $q->where('organization_id', $resolved['organization_id']);
                        if (Schema::hasColumn('user_permissions', 'branch_id')) {
                            if ($resolved['branch_id'] !== null) {
                                $q->where('branch_id', $resolved['branch_id']);
                            } else {
                                $q->whereNull('branch_id');
                            }
                        }
                        if (Schema::hasColumn('user_permissions', 'team_id')) {
                            if ($resolved['team_id'] !== null) {
                                $q->where('team_id', $resolved['team_id']);
                            } else {
                                $q->whereNull('team_id');
                            }
                        }
                    },
                    fn ($q) => $q->whereNull('organization_id')
                )
                ->delete();

            $newPerms = $this->getAllPermissions($organizationId)->toArray();

            // Security Audit Trail (SRS Section 142)
            AuditLog::log(
                event: 'permission_revoked',
                auditable: $this,
                newValues: ['permissions' => $newPerms, 'revoked_permission' => $permModel->code, 'scope' => $resolved],
                oldValues: ['permissions' => $oldPerms],
                notes: "Revoked direct permission '{$permModel->code}' from user '{$this->name}'".($organizationId ? " for scope [{$organizationId}]" : ' (global)'),
                actorId: auth()->id()
            );
        }
    }

    public function syncPermissions(array $permissions, $organizationId = null): void
    {
        $oldPerms = $this->getAllPermissions($organizationId)->toArray();
        $resolved = $this->resolveScopeHierarchy($organizationId);

        DB::table('user_permissions')
            ->where('user_id', $this->id)
            ->when(
                $organizationId !== null,
                function ($q) use ($resolved) {
                    $q->where('organization_id', $resolved['organization_id']);
                    if (Schema::hasColumn('user_permissions', 'branch_id')) {
                        if ($resolved['branch_id'] !== null) {
                            $q->where('branch_id', $resolved['branch_id']);
                        } else {
                            $q->whereNull('branch_id');
                        }
                    }
                    if (Schema::hasColumn('user_permissions', 'team_id')) {
                        if ($resolved['team_id'] !== null) {
                            $q->where('team_id', $resolved['team_id']);
                        } else {
                            $q->whereNull('team_id');
                        }
                    }
                },
                fn ($q) => $q->whereNull('organization_id')
            )
            ->delete();

        foreach ($permissions as $permission) {
            $permId = $permission instanceof Permission ? $permission->id : Permission::where('code', $permission)->firstOrFail()->id;
            $pivotData = ['organization_id' => $resolved['organization_id']];
            if (Schema::hasColumn('user_permissions', 'branch_id')) {
                $pivotData['branch_id'] = $resolved['branch_id'];
            }
            if (Schema::hasColumn('user_permissions', 'team_id')) {
                $pivotData['team_id'] = $resolved['team_id'];
            }
            $this->permissions()->attach($permId, $pivotData);
        }

        $newPerms = $this->getAllPermissions($organizationId)->toArray();

        // Security Audit Trail (SRS Section 142)
        AuditLog::log(
            event: 'permissions_synced',
            auditable: $this,
            newValues: ['permissions' => $newPerms, 'scope' => $resolved],
            oldValues: ['permissions' => $oldPerms],
            notes: "Synced direct permissions for user '{$this->name}'",
            actorId: auth()->id()
        );
    }

    public function hasPermissionTo($permission, $organizationId = null): bool
    {
        // Platform Administrator has super-admin rights across all permissions
        if ($this->hasRole('Administrator', $organizationId)) {
            return true;
        }

        $permissionCode = $permission instanceof Permission ? $permission->code : $permission;
        $resolved = $this->resolveScopeHierarchy($organizationId);

        // Check direct permissions (scoped with hierarchy or global)
        $hasDirect = $this->permissions()
            ->where('code', $permissionCode)
            ->where(function ($query) use ($resolved, $organizationId) {
                if ($organizationId !== null) {
                    $query->where(function ($sub) use ($resolved) {
                        $sub->whereNull('user_permissions.organization_id');
                        if ($resolved['organization_id']) {
                            $sub->orWhere(function ($qOrg) use ($resolved) {
                                $qOrg->where('user_permissions.organization_id', $resolved['organization_id']);
                                if (Schema::hasColumn('user_permissions', 'branch_id')) {
                                    $qOrg->whereNull('user_permissions.branch_id');
                                }
                            });
                        }
                        if ($resolved['branch_id'] && Schema::hasColumn('user_permissions', 'branch_id')) {
                            $sub->orWhere(function ($qBranch) use ($resolved) {
                                $qBranch->where('user_permissions.branch_id', $resolved['branch_id']);
                                if (Schema::hasColumn('user_permissions', 'team_id')) {
                                    $qBranch->whereNull('user_permissions.team_id');
                                }
                            });
                        }
                        if ($resolved['team_id'] && Schema::hasColumn('user_permissions', 'team_id')) {
                            $sub->orWhere('user_permissions.team_id', $resolved['team_id']);
                        }
                    });
                } else {
                    $query->whereNull('user_permissions.organization_id');
                }
            })
            ->exists();

        if ($hasDirect) {
            return true;
        }

        // Check through assigned roles (scoped with hierarchy or global)
        return $this->roles()
            ->where(function ($query) use ($resolved, $organizationId) {
                if ($organizationId !== null) {
                    $query->where(function ($sub) use ($resolved) {
                        $sub->whereNull('user_roles.organization_id');
                        if ($resolved['organization_id']) {
                            $sub->orWhere(function ($qOrg) use ($resolved) {
                                $qOrg->where('user_roles.organization_id', $resolved['organization_id']);
                                if (Schema::hasColumn('user_roles', 'branch_id')) {
                                    $qOrg->whereNull('user_roles.branch_id');
                                }
                            });
                        }
                        if ($resolved['branch_id'] && Schema::hasColumn('user_roles', 'branch_id')) {
                            $sub->orWhere(function ($qBranch) use ($resolved) {
                                $qBranch->where('user_roles.branch_id', $resolved['branch_id']);
                                if (Schema::hasColumn('user_roles', 'team_id')) {
                                    $qBranch->whereNull('user_roles.team_id');
                                }
                            });
                        }
                        if ($resolved['team_id'] && Schema::hasColumn('user_roles', 'team_id')) {
                            $sub->orWhere('user_roles.team_id', $resolved['team_id']);
                        }
                    });
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
            if (! empty($perm) && $this->hasPermissionTo($perm, $organizationId)) {
                return true;
            }
        }

        return false;
    }

    public function getRoleNames($organizationId = null): Collection
    {
        $resolved = $this->resolveScopeHierarchy($organizationId);

        return $this->roles()
            ->when($organizationId !== null, function ($query) use ($resolved) {
                $query->where(function ($sub) use ($resolved) {
                    $sub->whereNull('user_roles.organization_id');
                    if ($resolved['organization_id']) {
                        $sub->orWhere(function ($qOrg) use ($resolved) {
                            $qOrg->where('user_roles.organization_id', $resolved['organization_id']);
                            if (Schema::hasColumn('user_roles', 'branch_id')) {
                                $qOrg->whereNull('user_roles.branch_id');
                            }
                        });
                    }
                    if ($resolved['branch_id'] && Schema::hasColumn('user_roles', 'branch_id')) {
                        $sub->orWhere(function ($qBranch) use ($resolved) {
                            $qBranch->where('user_roles.branch_id', $resolved['branch_id']);
                            if (Schema::hasColumn('user_roles', 'team_id')) {
                                $qBranch->whereNull('user_roles.team_id');
                            }
                        });
                    }
                    if ($resolved['team_id'] && Schema::hasColumn('user_roles', 'team_id')) {
                        $sub->orWhere('user_roles.team_id', $resolved['team_id']);
                    }
                });
            })
            ->when($organizationId === null, fn ($q) => $q->whereNull('user_roles.organization_id'))
            ->pluck('name');
    }

    public function getAllPermissions($organizationId = null): Collection
    {
        $resolved = $this->resolveScopeHierarchy($organizationId);

        $direct = $this->permissions()
            ->when($organizationId !== null, function ($query) use ($resolved) {
                $query->where(function ($sub) use ($resolved) {
                    $sub->whereNull('user_permissions.organization_id');
                    if ($resolved['organization_id']) {
                        $sub->orWhere(function ($qOrg) use ($resolved) {
                            $qOrg->where('user_permissions.organization_id', $resolved['organization_id']);
                            if (Schema::hasColumn('user_permissions', 'branch_id')) {
                                $qOrg->whereNull('user_permissions.branch_id');
                            }
                        });
                    }
                    if ($resolved['branch_id'] && Schema::hasColumn('user_permissions', 'branch_id')) {
                        $sub->orWhere(function ($qBranch) use ($resolved) {
                            $qBranch->where('user_permissions.branch_id', $resolved['branch_id']);
                            if (Schema::hasColumn('user_permissions', 'team_id')) {
                                $qBranch->whereNull('user_permissions.team_id');
                            }
                        });
                    }
                    if ($resolved['team_id'] && Schema::hasColumn('user_permissions', 'team_id')) {
                        $sub->orWhere('user_permissions.team_id', $resolved['team_id']);
                    }
                });
            })
            ->when($organizationId === null, fn ($q) => $q->whereNull('user_permissions.organization_id'))
            ->pluck('code');

        $viaRoles = Permission::whereHas('roles', function ($query) use ($organizationId, $resolved) {
            $query->whereIn('roles.id', function ($sub) use ($organizationId, $resolved) {
                $sub->select('role_id')
                    ->from('user_roles')
                    ->where('user_id', $this->id)
                    ->when($organizationId !== null, function ($q) use ($resolved) {
                        $q->where(function ($sub2) use ($resolved) {
                            $sub2->whereNull('user_roles.organization_id');
                            if ($resolved['organization_id']) {
                                $sub2->orWhere(function ($qOrg) use ($resolved) {
                                    $qOrg->where('user_roles.organization_id', $resolved['organization_id']);
                                    if (Schema::hasColumn('user_roles', 'branch_id')) {
                                        $qOrg->whereNull('user_roles.branch_id');
                                    }
                                });
                            }
                            if ($resolved['branch_id'] && Schema::hasColumn('user_roles', 'branch_id')) {
                                $sub2->orWhere(function ($qBranch) use ($resolved) {
                                    $qBranch->where('user_roles.branch_id', $resolved['branch_id']);
                                    if (Schema::hasColumn('user_roles', 'team_id')) {
                                        $qBranch->whereNull('user_roles.team_id');
                                    }
                                });
                            }
                            if ($resolved['team_id'] && Schema::hasColumn('user_roles', 'team_id')) {
                                $sub2->orWhere('user_roles.team_id', $resolved['team_id']);
                            }
                        });
                    })
                    ->when($organizationId === null, fn ($q) => $q->whereNull('organization_id'));
            });
        })->pluck('code');

        return $direct->merge($viaRoles)->unique()->values();
    }
}
