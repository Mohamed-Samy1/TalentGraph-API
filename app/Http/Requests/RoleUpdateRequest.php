<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleId = $this->route('role')?->id;
        return [
            'name' => ['required', 'string', 'max:50', 'unique:roles,name,' . $roleId],
        ];
    }

    public function mappedAttributes(): array
    {
        return [
            'name' => $this->input('name'),
        ];
    }
}


