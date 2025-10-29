<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Hanya owner yang boleh melihat daftar user.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('owner');
    }

    /**
     * Hanya owner yang boleh melihat detail user.
     */
    public function view(User $user, User $model): bool
    {
        return $user->hasRole('owner');
    }

    /**
     * Hanya owner yang boleh membuat user baru.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('owner');
    }

    /**
     * Hanya owner yang boleh update user.
     */
    public function update(User $user, User $model): bool
    {
        return $user->hasRole('owner');
    }

    /**
     * Hanya owner yang boleh menghapus user.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->hasRole('owner');
    }

    /**
     * Tidak ada yang boleh restore (opsional).
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Tidak ada yang boleh force delete (opsional).
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
