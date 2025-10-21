<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', 'unique:roles,name'],
        ];
    }

    public function mappedAttributes(): array
    {
        return [
            'name' => $this->input('name'),
        ];
    }
}


