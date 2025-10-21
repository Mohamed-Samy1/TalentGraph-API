<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponses;

use App\Http\Resources\UserResource;

use App\Http\Controllers\Controller;

use App\Http\Requests\ApiLoginRequest;
use App\Http\Requests\ApiRegisterRequest;
use App\Http\Requests\PasswordResetRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use App\Models\Role;

use App\Permissions\Abilities;

class AuthController extends Controller
{
    use ApiResponses;

    public function login(ApiLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember', false);

        // Attempt login with credentials only
        if (!Auth::attempt($credentials)) {
            return $this->error('Invalid credentials', 401);
        }

        $user = User::firstWhere('email', $request->email);

        // Define token lifetime based on remember flag
        $expiry = $remember ? now()->addMonths(6) : now()->addWeek();

        $token = $user->createToken(
            'API token for ' . $user->email,
            Abilities::getAbilities($user),
            $expiry
        )->plainTextToken;

        return $this->ok('Authenticated', [
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => $expiry->toDateTimeString(),
            'remember' => $remember,
        ]);
    }

    public function logout(Request $request) {
        $token = $request->user()->currentAccessToken();

        if ($token) {
            $token->delete();
            return $this->ok('Logged out successfully');
        }

        return $this->error('No active token found', 400);
    }

    public function register(ApiRegisterRequest $request): JsonResponse
    {
        try {
            $attributes = $request->mappedAttributes();
            $requestedRoleName = $attributes['role'];

            // Only admin can create admin users
            if ($requestedRoleName === 'admin') {
                $user = auth()->user();
                if (!$user || !$user->isAdmin()) {
                    return $this->error('Only admin can create admin users', 403);
                }
            }

            $role = Role::where('name', $requestedRoleName)->first();
            if (!$role) {
                return $this->error('Invalid role provided', 422);
            }

            $user = User::create([
                'role_id' => $role->id,
                'name' => $attributes['name'],
                'email' => $attributes['email'],
                'phone' => $attributes['phone'] ?? null,
                'password' => Hash::make($attributes['password']),
            ]);

            return $this->created('User registered successfully', [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $role->name,
                ]
            ]);
        } catch (\Exception $ex) {
            return $this->error('Registration failed: ' . $ex->getMessage(), 500);
        }
    }

    public function forgot(Request $request): JsonResponse
    {
        $request->validate([
        'email' => 'required|email|exists:users,email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? $this->ok('Reset link sent successfully.')
            : $this->error(__($status), 500);
    }

    public function reset(PasswordResetRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? $this->ok('Password has been reset successfully.')
            : $this->error(__($status), 400);
    }
}


    
