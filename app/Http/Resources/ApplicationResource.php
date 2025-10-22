<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $application->id,
            'vacancy_title' => $vacancy->title,
            'company_name' => $vacancy->company->name,
            'applied_at' => $application->applied_at,
            'status' => $application->status,
        ];
    }
}