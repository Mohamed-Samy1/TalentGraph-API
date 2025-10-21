<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponses;
use App\Http\Requests\VacancyStoreRequest;
use App\Http\Requests\VacancyUpdateRequest;
use App\Http\Resources\VacancyResource;
use App\Models\Vacancy;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VacancyController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $vacancies = Vacancy::with(['company', 'employer'])->get();
        
        return $this->ok('Vacancies fetched successfully', [
            'vacancies' => VacancyResource::collection($vacancies)
        ]);
    }

    public function store(VacancyStoreRequest $request)
    {
        $user = auth()->user();
        
        if (!$user->isEmployer()) {
            return $this->error('Only employers can create vacancies', 403);
        }

        if (!$user->company) {
            return $this->error('You must have a company to create vacancies', 403);
        }

        // Check for duplicate title for the same employer
        $existingVacancy = Vacancy::where('employer_id', $user->id)
            ->where('title', $request->input('title'))
            ->first();
            
        if ($existingVacancy) {
            return $this->error('You have already posted a vacancy with this title', 422);
        }

        $attributes = $request->mappedAttributes();
        $attributes['employer_id'] = $user->id;
        $attributes['company_id'] = $user->company->id;
        
        $vacancy = Vacancy::create($attributes);
        
        $vacancy->load(['company', 'employer']);
        
        return $this->created('Vacancy created successfully', [
            'vacancy' => new VacancyResource($vacancy)
        ]);
    }

    public function show(Vacancy $vacancy)
    {
        $vacancy->load(['company', 'employer']);
        
        return $this->ok('Vacancy fetched successfully', [
            'vacancy' => new VacancyResource($vacancy)
        ]);
    }

    public function update(VacancyUpdateRequest $request, Vacancy $vacancy)
    {
        $user = auth()->user();
        
        // Check if user is the employer who created this vacancy
        if ($vacancy->employer_id !== $user->id) {
            return $this->error('You can only update your own vacancies', 403);
        }

        // Check for duplicate title if title is being updated
        if ($request->has('title') && $request->input('title') !== $vacancy->title) {
            $existingVacancy = Vacancy::where('employer_id', $user->id)
                ->where('title', $request->input('title'))
                ->where('id', '!=', $vacancy->id)
                ->first();
                
            if ($existingVacancy) {
                return $this->error('You have already posted a vacancy with this title', 422);
            }
        }

        $attributes = $request->mappedAttributes();
        $vacancy->update($attributes);
        
        // Load relationships for the response
        $vacancy->load(['company', 'employer']);
        
        return $this->ok('Vacancy updated successfully', [
            'vacancy' => new VacancyResource($vacancy)
        ]);
    }

    public function destroy(Vacancy $vacancy)
    {
        $user = auth()->user();
        
        // Check if user is the employer who created this vacancy
        if ($vacancy->employer_id !== $user->id) {
            return $this->error('You can only delete your own vacancies', 403);
        }

        $vacancy->delete();
        
        return $this->ok('Vacancy deleted successfully');
    }
}
