<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponses;

use App\Http\Requests\VacancyStoreRequest;
use App\Http\Requests\VacancyUpdateRequest;

use App\Http\Resources\VacancyResource;

use App\Http\Filters\VacancyFilter;

use App\Models\Vacancy;
use App\Models\Company;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;

class VacancyController extends Controller
{
    use ApiResponses;
    use SoftDeletes;

    public function index(Request $request)
    {
        try {
            
            $filters = new VacancyFilter($request);
        
            $vacancies = Vacancy::filter($filters)
                ->with(['company', 'employer'])
                ->paginate();

        } catch (\Throwable $e) {
            return $this->error('Failed to fetch vacancies', 500);
        }   
    }

    public function store(VacancyStoreRequest $request)
    {
        try {

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

            activity()
            ->causedBy(auth()->user())
            ->performedOn($vacancy)
            ->log('Vacancy created by employer');
            
            return $this->ok('Vacancy created successfully', [
                'vacancy' => new VacancyResource($vacancy)
            ], 201);

        } catch (\Throwable $e) {
            return $this->error('Failed to create vacancy', 500);
        }
    }

    public function show(Vacancy $vacancy)
    {
        try {

            $vacancy->load(['company', 'employer']);
        
            return $this->ok('Vacancy fetched successfully', [
                'vacancy' => new VacancyResource($vacancy)
            ]);

        } catch (\Throwable $e) {
            return $this->error('Failed to fetch the vacancy data', 500);
        }
    }

    public function update(VacancyUpdateRequest $request, Vacancy $vacancy)
    {
        try {

            $user = auth()->user();
        
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
            
            $vacancy->load(['company', 'employer']);
            
            return $this->ok('Vacancy updated successfully', [
                'vacancy' => new VacancyResource($vacancy)
            ]);
        } catch (\Throwable $e) {
            return $this->error('Failed to update Vacancy', 500);
        }
    }

    public function destroy(Vacancy $vacancy)
    {
        try {

            $user = auth()->user();
        
            if ($vacancy->employer_id !== $user->id) {
                return $this->error('You can only delete your own vacancies', 403);
            }

            $vacancy->delete();

            activity()
            ->causedBy(auth()->user())
            ->performedOn($vacancy)
            ->log('Vacancy deleted by employer');
            
            return $this->ok('Vacancy soft deleted successfully', 200);

        } catch (\Throwable $e) {
            return $this->error('Failed to delete vacancy', 500);
        }
    }
}
