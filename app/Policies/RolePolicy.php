<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->isAdmin();
    }

    public function view(User $user, Role $role)
    {
        return $user->isAdmin();
    }

    public function store(User $user)
    {
        return $user->isAdmin();
    }

    public function update(User $user, Role $role)
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Role $role)
    {
        return $user->isAdmin();
    }
}
