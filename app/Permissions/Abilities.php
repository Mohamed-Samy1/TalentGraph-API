<?php

namespace App\Permissions;

use App\Models\User;

final class Abilities
{
    // User Abilities
    public const CreateUser = 'user:create';
    public const UpdateUser = 'user:update';
    public const DeleteUser = 'user:delete';
    public const ViewUser = 'user:view';

    // Role Abilities
    public const CreateRole = 'role:create';
    public const UpdateRole = 'role:update';
    public const DeleteRole = 'role:delete';
    public const ViewRole = 'role:view';

    // Company Abilities
    public const CreateCompany = 'company:create';
    public const UpdateCompany = 'company:update';
    public const DeleteCompany = 'company:delete';
    public const ViewCompany = 'company:view';

    // Vacancy Abilities
    public const CreateVacancy = 'vacancy:create';
    public const UpdateVacancy = 'vacancy:update';
    public const DeleteVacancy = 'vacancy:delete';
    public const ViewVacancy = 'vacancy:view';

    // Application Abilities
    public const CreateApplication = 'application:create';
    public const UpdateApplication = 'application:update';
    public const DeleteApplication = 'application:delete';
    public const ViewApplication = 'application:view';
    public const WithdrawApplication = 'application:withdraw';

    public static function getAbilities(User $user)
    {
        if ($user->isAdmin()) {
            return [
                self::CreateUser,
                self::UpdateUser,
                self::DeleteUser,
                self::ViewUser,
                self::CreateRole,
                self::UpdateRole,
                self::DeleteRole,
                self::ViewRole,
                self::CreateCompany,
                self::UpdateCompany,
                self::DeleteCompany,
                self::ViewCompany,
                self::CreateVacancy,
                self::UpdateVacancy,
                self::DeleteVacancy,
                self::ViewVacancy,
                self::CreateApplication,
                self::UpdateApplication,
                self::DeleteApplication,
                self::ViewApplication,
                self::WithdrawApplication,
            ];
        } elseif ($user->isEmployer()) {
            return [
                self::ViewUser,
                self::CreateCompany,
                self::UpdateCompany,
                self::ViewCompany,
                self::CreateVacancy,
                self::UpdateVacancy,
                self::DeleteVacancy,
                self::ViewVacancy,
                self::ViewApplication,
                self::UpdateApplication,
            ];
        } else {
            return [
                self::ViewUser,
                self::ViewCompany,
                self::ViewVacancy,
                self::CreateApplication,
                self::ViewApplication,
                self::WithdrawApplication,
            ];
        }
    }
}