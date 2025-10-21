<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Company;
use Illuminate\Auth\Access\HandlesAuthorization;

class CompanyPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Company $company)
    {
        return true;
    }

    public function store(User $user)
    {
        return $user->isEmployer();
    }

    public function update(User $user, Company $company)
    {
        return $user->isAdmin() || ($user->isEmployer() && $user->id === $company->employer_id);
    }

    public function delete(User $user, Company $company)
    {
        return $user->isAdmin() || ($user->isEmployer() && $user->id === $company->employer_id);
    }
}
