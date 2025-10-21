<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyUpdateRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:50'],
            'description' => ['sometimes', 'required', 'string', 'max:1000'],
            'website' => ['sometimes', 'required', 'url', 'max:255'],
            'logo_path' => ['sometimes', 'required', 'string', 'max:2048'],
            'logo_size' => ['sometimes', 'required', 'integer', 'max:10485760'], // max 10MB
            'is_verified' => ['sometimes', 'required', 'boolean']
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Company name is required.',
            'name.max' => 'Company name must not exceed 50 characters.',
            'description.required' => 'Company description is required.',
            'description.max' => 'Company description must not exceed 1000 characters.',
            'website.required' => 'Website URL is required.',
            'website.url' => 'Please provide a valid website URL.',
            'website.max' => 'Website URL must not exceed 255 characters.',
            'logo_path.required' => 'Logo path is required.',
            'logo_path.max' => 'Logo path must not exceed 2048 characters.',
            'logo_size.required' => 'Logo size is required.',
            'logo_size.integer' => 'Logo size must be a number.',
            'logo_size.max' => 'Logo size must not exceed 10MB.',
            'is_verified.required' => 'Verification status is required.',
            'is_verified.boolean' => 'Verification status must be true or false.',
        ];
    }

    public function mappedAttributes(): array
    {
        $attributes = [];
        
        if ($this->has('name')) {
            $attributes['name'] = $this->input('name');
        }
        
        if ($this->has('description')) {
            $attributes['description'] = $this->input('description');
        }
        
        if ($this->has('website')) {
            $attributes['website'] = $this->input('website');
        }
        
        if ($this->has('logo_path')) {
            $attributes['logo_path'] = $this->input('logo_path');
        }
        
        if ($this->has('logo_size')) {
            $attributes['logo_size'] = $this->input('logo_size');
        }
        
        if ($this->has('is_verified')) {
            $attributes['is_verified'] = $this->input('is_verified');
        }
        
        return $attributes;
    }
}
