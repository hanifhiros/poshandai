<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use App\Models\Role;

class RoleHelper
{
    // Cek apakah user punya role atau turunannya
    public static function hasAnyRoleIncludingDescendants(array $targetRoleNames)
    {
        $user = Auth::user();
        if (!$user) return false;

        $userRoles = $user->roles;

        foreach ($userRoles as $role) {
            if (in_array($role->name, $targetRoleNames) ||in_array($role->name, ['Superadmin'])) {
                return true;
            }

            if (self::hasParentInTargets($role, $targetRoleNames)) {
                return true;
            }
        }

        return false;
    }

    // Traverse parent role sampai ke atas
    protected static function hasParentInTargets($role, array $targetRoleNames)
    {
        while ($role && $role->parent_id) {
            $role = $role->parent; // pakai relasi parent() di Role model
            if (!$role) {
                break;
            }

            if (in_array($role->name, $targetRoleNames)) {
                return true;
            }
        }

        return false;
    }

    // Cek role exact
    public static function hasRole($targetRoleName)
    {
        $user = Auth::user();
        if (!$user) return false;

        return $user->roles->contains('name', $targetRoleName)||$user->roles->contains('name', 'Superadmin');
    }
}
