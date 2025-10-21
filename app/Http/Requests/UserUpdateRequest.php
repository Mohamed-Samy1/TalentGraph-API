<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $userId],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['sometimes', 'string', 'min:6'],
            'role' => ['sometimes', 'in:job_seeker,employer,admin'],
        ];
    }

    public function mappedAttributes(): array
    {
        $attributes = [];
        
        if ($this->has('name')) {
            $attributes['name'] = $this->input('name');
        }
        if ($this->has('email')) {
            $attributes['email'] = $this->input('email');
        }
        if ($this->has('phone')) {
            $attributes['phone'] = $this->input('phone');
        }
        if ($this->has('password')) {
            $attributes['password'] = $this->input('password');
        }
        if ($this->has('role')) {
            $attributes['role'] = $this->input('role');
        }

        return $attributes;
    }
}
