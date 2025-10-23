<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; 

class ApplicationUpdateRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'sometimes',          
                'required',
                'string',
                Rule::in(['pending', 'reviewed', 'accepted', 'rejected']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'You did not provide the new status of that application.',
            'status.in' => 'The provided status is invalid. Allowed values: pending, reviewed, accepted, rejected.',
        ];
    }

    public function mappedAttributes(): array
    {
        $attributes = [];
        
        if ($this->has('status')) {
            $attributes['status'] = $this->input('status');
        }
        
        return $attributes;
    }
}
