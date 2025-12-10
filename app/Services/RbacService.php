<?php

namespace App\Services;

use App\Models\AuthAssignment;
use App\Models\AuthItemChild;
use Illuminate\Support\Facades\Cache;

class RbacService
{
    /**
     * Check if a user has a specific permission.
     *
     * @param int $userId
     * @param string $permissionName
     * @return bool
     */
    public function checkPermission($userId, $permissionName)
    {
        // 1. Get all roles assigned to the user
        $userRoles = AuthAssignment::where('user_id', $userId)->pluck('item_name')->toArray();

        if (empty($userRoles)) {
            return false;
        }

        // 2. Check if the user has the permission directly (if it's assigned as a role, though unlikely for permissions)
        if (in_array($permissionName, $userRoles)) {
            return true;
        }

        // 3. Check hierarchy
        // We need to see if $permissionName is a child (descendant) of any of $userRoles.
        // To avoid deep recursion on every check, we can cache the permissions for each role.
        
        return $this->hasPermissionRecursive($userRoles, $permissionName);
    }

    protected function hasPermissionRecursive($parentItems, $targetItem)
    {
        // Get immediate children of the parent items
        $children = AuthItemChild::whereIn('parent', $parentItems)->pluck('child')->toArray();

        if (empty($children)) {
            return false;
        }

        if (in_array($targetItem, $children)) {
            return true;
        }

        // Recurse
        return $this->hasPermissionRecursive($children, $targetItem);
    }
}
