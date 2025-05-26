<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PermissionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('permission_access');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Permission $permission): bool
    {
        return $user->hasPermission('permission_show');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('permission_create');

    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Permission $permission): bool
    {
        return $user->hasPermission('permission_edit');

    }

    public function delete(User $user, Permission $permission): bool
    {
        return $user->hasPermission('permission_delete');

    }

    /**
     * Determine whether the user can delete the model.
     */
    public function deleteAny(User $user)
    {
        return $user->hasPermission('permission_delete');

    }
}
