<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Policies\RolePolicy;
use App\Traits\ApiResponses;
use App\Http\Requests\RoleStoreRequest;
use App\Http\Requests\RoleUpdateRequest;
use App\Http\Resources\RoleResource;
use Illuminate\Auth\Access\AuthorizationException;

class RoleController extends Controller
{
    use ApiResponses;

    public function index()
    {
        try {
            $user = auth()->user();
            $policy = app(RolePolicy::class);
            $canViewAny = $policy->viewAny($user);

            if (!$canViewAny) {
                return $this->error('You are not authorized to view roles', 403);
            }

            $roles = Role::orderBy('id')->get();
            return $this->ok('Roles fetched', RoleResource::collection($roles));
        } catch (AuthorizationException $ex) {
            return $this->error('You are not authorized to view roles', 403);
        }
    }

    public function store(RoleStoreRequest $request)
    {
        try {
            $user = auth()->user();
            $policy = app(RolePolicy::class);
            $canCreate = $policy->store($user);

            if (!$canCreate) {
                return $this->error('You are not authorized to create roles', 403);
            }

            $attributes = $request->mappedAttributes();
            $role = Role::create($attributes);
            
            return $this->created('Role created', new RoleResource($role));
        } catch (AuthorizationException $ex) {
            return $this->error('You are not authorized to create roles', 403);
        }
    }

    public function show(Role $role)
    {
        try {
            $user = auth()->user();
            $policy = app(RolePolicy::class);
            $canView = $policy->view($user, $role);

            if (!$canView) {
                return $this->error('You are not authorized to view this role', 403);
            }

            return $this->ok('Role fetched', new RoleResource($role));
        } catch (AuthorizationException $ex) {
            return $this->error('You are not authorized to view this role', 403);
        }
    }

    public function update(RoleUpdateRequest $request, Role $role)
    {
        try {
            $user = auth()->user();
            $policy = app(RolePolicy::class);
            $canUpdate = $policy->update($user, $role);

            if (!$canUpdate) {
                return $this->error('You are not authorized to update this role', 403);
            }

            $attributes = $request->mappedAttributes();
            $role->update($attributes);
            
            return $this->ok('Role updated', new RoleResource($role));
        } catch (AuthorizationException $ex) {
            return $this->error('You are not authorized to update this role', 403);
        }
    }

    public function destroy(Role $role)
    {
        try {
            $user = auth()->user();
            $policy = app(RolePolicy::class);
            $canDelete = $policy->delete($user, $role);

            if (!$canDelete) {
                return $this->error('You are not authorized to delete this role', 403);
            }

            $role->delete();
            return $this->ok('Role deleted');
        } catch (AuthorizationException $ex) {
            return $this->error('You are not authorized to delete this role', 403);
        }
    }
}


