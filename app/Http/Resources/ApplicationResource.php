<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vacancy_title' => $this->vacancy->title ?? null,
            'company_name' => $this->vacancy->company->name ?? null,
            'applied_at' => $this->applied_at,
            'status' => $this->status,
        ];
    }
}