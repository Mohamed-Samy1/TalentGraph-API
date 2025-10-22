<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplicationStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isJobSeeker();
    }

    public function rules(): array
    {
        return [
            'resume' => ['required', 'string', 'max:255'], 
            'cover_letter' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'resume.required' => 'A resume path is required.',
            'resume.string' => 'The resume must be a valid file path.',
            'resume.max' => 'The resume path must not exceed 255 characters.',
            'cover_letter.max' => 'The cover letter must not exceed 5000 characters.',
        ];
    }

    public function mappedAttributes(): array
    {
        $resumePath = $this->input('resume');
        $resumeName = basename($resumePath);
        
        $resumeSize = 0;
        if (file_exists($resumePath)) {
            $resumeSize = filesize($resumePath);
        }

        return [
            'resume_path' => $resumePath,
            'resume_name' => $resumeName,
            'resume_size' => $resumeSize,
            'cover_letter' => $this->input('cover_letter'),
            'status' => 'pending',
            'withdrawn' => false,
            'applied_at' => now(),
        ];
    }
}

    
