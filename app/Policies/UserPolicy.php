<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * سياسة التحكم في المستخدمين
 * User Policy - Admin only
 */
class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any users.
     * Admin only
     */
    public function viewAny(User $user): bool
    {
        return $user->can('users.view-any');
    }

    /**
     * Determine whether the user can view a user.
     * Admin only
     */
    public function view(User $user, User $model): bool
    {
        return $user->can('users.view');
    }

    /**
     * Determine whether the user can create users.
     * Admin only
     */
    public function create(User $user): bool
    {
        return $user->can('users.create');
    }

    /**
     * Determine whether the user can update a user.
     * Admin only
     */
    public function update(User $user, User $model): bool
    {
        return $user->can('users.update');
    }

    /**
     * Users should not be deleted, only deactivated.
     * Return false to prevent deletion.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->can('users.delete');
    }
}
