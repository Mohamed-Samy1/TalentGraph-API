<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Policies\UserPolicy;
use Illuminate\Http\Request;
use App\Traits\ApiResponses;
use App\Http\Resources\UserResource;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use ApiResponses;

    public function index()
    {
        try {

            $user = auth()->user();
            $policy = app(UserPolicy::class);
            $canViewAny = $policy->viewAny($user);

            if (!$canViewAny) {
                return $this->error('Only the admin can view users', 403);
            }

            $users = User::with('role')->orderBy('id')->paginate(20);
            return $this->ok('Users fetched', [
                'data' => UserResource::collection($users)
            ]);

        } catch (\Throwable $e) {
            return $this->error('You are not authorized to view users', 403);
        }
    }

    public function show(User $user)
    {
        try {
            
            $currentUser = auth()->user();
            $policy = app(UserPolicy::class);
            $canView = $policy->view($currentUser, $user);

            if (!$canView) {
                return $this->error('You are not authorized to view this user', 403);
            }

            $user->load('role');
            return $this->ok('User fetched', new UserResource($user));
        
        } catch (\Throwable $e) {
            return $this->error('You are not authorized to view this user', 403);
        }
    }

    public function store(UserStoreRequest $request)
    {
        try {
            $user = auth()->user();
            $policy = app(UserPolicy::class);
            $canCreate = $policy->store($user);

            if (!$canCreate) {
                return $this->error('You are not authorized to create users', 403);
            }

            $attributes = $request->mappedAttributes();
            
            // Check if trying to create admin user
            if ($attributes['role'] === 'admin') {
                $canCreateAdmin = $policy->createAdmin($user);
                if (!$canCreateAdmin) {
                    return $this->error('Only admin can create admin users', 403);
                }
            }

            $role = Role::where('name', $attributes['role'])->firstOrFail();
            
            $user = User::create([
                'role_id' => $role->id,
                'name' => $attributes['name'],
                'email' => $attributes['email'],
                'phone' => $attributes['phone'] ?? null,
                'password' => Hash::make($attributes['password']),
            ]);

            return $this->created('User created', new UserResource($user->load('role')));
        
        } catch (\Throwable $e) {
            return $this->error('You are not authorized to create users', 403);
        }
    }

    public function update(UserUpdateRequest $request, User $user)
    {
        try {
            $currentUser = auth()->user();
            $policy = app(UserPolicy::class);
            $canUpdate = $policy->update($currentUser, $user);

            if (!$canUpdate) {
                return $this->error('You are not authorized to update this user', 403);
            }

            $attributes = $request->mappedAttributes();
            
            // Check if trying to assign admin role
            if (isset($attributes['role']) && $attributes['role'] === 'admin') {
                $canCreateAdmin = $policy->createAdmin($currentUser);
                if (!$canCreateAdmin) {
                    return $this->error('Only admin can assign admin role', 403);
                }
            }

            if (isset($attributes['role'])) {
                $role = Role::where('name', $attributes['role'])->firstOrFail();
                $user->role_id = $role->id;
            }

            if (isset($attributes['name'])) {
                $user->name = $attributes['name'];
            }
            if (isset($attributes['email'])) {
                $user->email = $attributes['email'];
            }
            if (array_key_exists('phone', $attributes)) {
                $user->phone = $attributes['phone'];
            }
            if (isset($attributes['password'])) {
                $user->password = Hash::make($attributes['password']);
            }
            
            $user->save();

            return $this->ok('User updated', new UserResource($user->load('role')));
        } catch (\Throwable $e) {
            return $this->error('You are not authorized to update this user', 403);
        }
    }

    public function destroy(User $user)
    {
        try {
            $currentUser = auth()->user();
            $policy = app(UserPolicy::class);
            $canDelete = $policy->delete($currentUser, $user);

            if (!$canDelete) {
                return $this->error('You are not authorized to delete this user', 403);
            }

            $user->delete();
            return $this->ok('User deleted');

        } catch (\Throwable $e) {
            return $this->error('You are not authorized to delete this user', 403);
        }
    }
}


