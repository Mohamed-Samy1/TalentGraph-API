<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VacancyUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:100'],
            'description' => ['sometimes', 'required', 'string', 'max:2000'],
            'location' => ['sometimes', 'required', 'string', 'max:255'],
            'salary_min' => ['sometimes', 'required', 'string', 'max:100'],
            'salary_max' => ['sometimes', 'required', 'string', 'max:100'],
            'job_type' => ['sometimes', 'required', 'string', 'in:full_time,part_time,contract,freelance,internship'],
            'is_filled' => ['sometimes', 'required', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Job title is required.',
            'title.max' => 'Job title must not exceed 100 characters.',
            'description.required' => 'Job description is required.',
            'description.max' => 'Job description must not exceed 2000 characters.',
            'location.required' => 'Job location is required.',
            'location.max' => 'Job location must not exceed 255 characters.',
            'salary_min.required' => 'Minimum salary is required.',
            'salary_min.max' => 'Minimum salary must not exceed 100 characters.',
            'salary_max.required' => 'Maximum salary is required.',
            'salary_max.max' => 'Maximum salary must not exceed 100 characters.',
            'job_type.required' => 'Job type is required.',
            'job_type.in' => 'Job type must be one of: full_time, part_time, contract, freelance, internship.',
            'is_filled.required' => 'Filled status is required.',
            'is_filled.boolean' => 'Filled status must be true or false.',
        ];
    }

    /**
     * Get the mapped attributes for the request.
     *
     * @return array<string, mixed>
     */
    public function mappedAttributes(): array
    {
        $attributes = [];
        
        if ($this->has('title')) {
            $attributes['title'] = $this->input('title');
        }
        
        if ($this->has('description')) {
            $attributes['description'] = $this->input('description');
        }
        
        if ($this->has('location')) {
            $attributes['location'] = $this->input('location');
        }
        
        if ($this->has('salary_min')) {
            $attributes['salary_min'] = $this->input('salary_min');
        }
        
        if ($this->has('salary_max')) {
            $attributes['salary_max'] = $this->input('salary_max');
        }
        
        if ($this->has('job_type')) {
            $attributes['job_type'] = $this->input('job_type');
        }
        
        if ($this->has('is_filled')) {
            $attributes['is_filled'] = $this->input('is_filled');
        }
        
        return $attributes;
    }
}
