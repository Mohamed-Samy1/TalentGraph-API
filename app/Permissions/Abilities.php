<?php

namespace App\Permissions;

use App\Models\User;

final class Abilities
{
    
    //User Abilities
    public const CreateUser = 'user:create';
    public const UpdateUser = 'user:update';
    public const ReplaceUser = 'user:replace';
    public const DeleteUser = 'user:delete';

    public static function getAbilities(User $user)
    {
        if ($user->role && $user->role->name === 'admin') {
            return [
                self::CreateUser,
                self::UpdateUser,
                self::ReplaceUser,
                self::DeleteUser,
            ];
        } else {
            return [
                
            ];
        }
    }
}