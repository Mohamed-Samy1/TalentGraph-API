<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'description' => ['required', 'string', 'max:1000'],
            'website' => ['required', 'url', 'max:255'],
            'logo_path' => ['required', 'string', 'max:2048'],
            'logo_size' => ['required', 'integer', 'max:10485760'], // max 10MB
            'is_verified' => ['required','boolean']

        ];
    }

    public function mappedAttributes(): array
    {
        return [
            'name' => $this->input('name'),
            'description' => $this->input('description'),
            'website' => $this->input('website'),
            'logo_path' => $this->input('logo_path'),
            'logo_size' => $this->input('logo_size'),
            'is_verified' => $this->input('is_verified', false),
        ];
    }
}


