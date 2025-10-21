<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VacancyStoreRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:2000'],
            'location' => ['required', 'string', 'max:255'],
            'salary_min' => ['required', 'string', 'max:100'],
            'salary_max' => ['required', 'string', 'max:100'],
            'job_type' => ['required', 'string', 'in:full_time,part_time,contract,freelance,internship'],
            'is_filled' => ['required', 'boolean'],
        ];
    }

    public function mappedAttributes(): array
    {
        return [
            'title' => $this->input('title'),
            'description' => $this->input('description'),
            'location' => $this->input('location'),
            'salary_min' => $this->input('salary_min'),
            'salary_max' => $this->input('salary_max'),
            'job_type' => $this->input('job_type'),
            'is_filled' => $this->input('is_filled', false),
        ];
    }
}
