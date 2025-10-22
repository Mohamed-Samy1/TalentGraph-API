<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'website' => $this->website,
            'logo_path' => $this->logo_path,
            'logo_size' => $this->logo_size,
            'is_verified' => $this->is_verified,
            'employer' => [
                'id' => $this->employer->id,
                'name' => $this->employer->name,
                'email' => $this->employer->email,
            ],
            'vacancies' => [
                'count' => $this->vacancies->count(),
                'data' => VacancyResource::collection($this->whenLoaded('vacancies')),
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
