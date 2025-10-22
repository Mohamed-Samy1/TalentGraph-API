<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplicationStoreRequest;
use App\Models\Application;
use App\Models\Vacancy;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ApplicationController extends Controller
{
    use ApiResponses;

    public function apply(ApplicationStoreRequest $request, Vacancy $vacancy)
    {
        $user = auth()->user();

        $existing = Application::where('user_id', $user->id)
            ->where('vacancy_id', $vacancy->id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'You have already applied to this job vacancy.',
                'success' => false
            ], 400);
        }

        $attributes = $request->mappedAttributes();
        $attributes['user_id'] = $user->id;
        $attributes['vacancy_id'] = $vacancy->id;

        $application = Application::create($attributes);
        $application->load(['vacancy.company', 'applicant']);

        return $this->ok('Application submitted successfully', [
            'vacancy' => new ApplicationResource($application)
        ], 201);
    }

    public function myApplications()
    {
        $user = auth()->user();
        
        $applications = $user->applications()
            ->with(['vacancy.company'])
            ->orderBy('applied_at', 'desc')
            ->get();

        return response()->json([
            'applications' => $applications->map(function ($application) {
                return [
                    'id' => $application->id,
                    'vacancy_title' => $application->vacancy->title,
                    'company_name' => $application->vacancy->company->name,
                    'location' => $application->vacancy->location,
                    'status' => $application->status,
                    'applied_at' => $application->applied_at,
                    'resume_name' => $application->resume_name,
                    'withdrawn' => $application->withdrawn,
                ];
            })
        ]);
    }

    public function withdraw(Application $application)
    {
        $user = auth()->user();

        if ($application->user_id !== $user->id) {
            return response()->json([
                'message' => 'Application not found or you are not authorized to withdraw this application.',
                'success' => false
            ], 404);
        }

        if ($application->withdrawn) {
            return response()->json([
                'message' => 'This application has already been withdrawn.',
                'success' => false
            ], 400);
        }

        $application->update(['withdrawn' => true]);

        return $this->ok('Application withdrawn successfully', 200);
    }
}
