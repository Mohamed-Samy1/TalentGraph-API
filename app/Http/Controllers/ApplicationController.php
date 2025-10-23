<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplicationStoreRequest;
use App\Http\Requests\ApplicationUpdateRequest;
use App\Http\Resources\ApplicationResource;
use App\Models\Application;
use App\Models\Vacancy;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use ZipArchive;

class ApplicationController extends Controller
{
    use ApiResponses;

    // --------------------------------
    // JOB SEEKER APPLICATION MANAGEMENT
    // --------------------------------

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
        $application->load(['vacancy.company', 'user']);

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

    public function togglewithdraw(Application $application)
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

    // --------------------------------
    // EMPLOYER APPLICATIONS MANAGEMENT
    // --------------------------------

    public function companyApplications()
    {
        $user = auth()->user();

        if (!$user->isEmployer()) {
            return $this->error('Only employers can view company applications', 403);
        }

        if (!$user->company) {
            return $this->error('You must have a company to view its applications', 403);
        }

        $applications = Application::whereHas('vacancy', function ($query) use ($user) {
            $query->where('company_id', $user->company->id);
        })
        ->where('withdrawn', false)
        ->with(['vacancy', 'user'])
        ->orderBy('applied_at', 'desc')
        ->get();

        return response()->json([
            'success' => true,
            'applications' => $applications->map(function ($application) {
                return [
                    'id' => $application->id,
                    'vacancy_title' => $application->vacancy->title,
                    'vacancy_id' => $application->vacancy->id,
                    'applicant_name' => $application->user->name,
                    'status' => $application->status,
                    'applied_at' => $application->applied_at,
                ];
            })
        ]);
    }

    public function showApplication(Application $application) {
        
        $user = auth()->user();

        if(!$user->isEmployer() || !$user->company()) {
                return $this->error('You must be an employer who has a company to get application info', 401);
            }

        if (!$application->vacancy || $application->vacancy->company_id !== $user->company->id) {
            return $this->error('You are not authorized to get this application.', 403);
        }

        $application->load(['user:id,name,email,phone']);

        $data = [
            'applicant' => [
                'name' => $application->user->name,
                'email' => $application->user->email,
                'phone' => $application->user->phone,
            ],
            'applied_at' => $application->applied_at,
        ];

        return $this->ok('Application details fetched successfully.', $data, 200);
    }

    public function downloadApplications()
    {
        $user = auth()->user();

        if (!$user->isEmployer()) {
            return $this->error('Only employers can download resumes.', 403);
        }

        if (!$user->company) {
            return $this->error('You must have a company to download resumes.', 403);
        }

        $applications = Application::whereHas('vacancy', function ($query) use ($user) {
            $query->where('company_id', $user->company->id);
        })
        ->where('withdrawn', false)
        ->with(['user', 'vacancy'])
        ->get();

        if ($applications->isEmpty()) {
            return $this->error('No applications found for your company.', 404);
        }

        // Compress resumes (from /public/resumes)
        $resumeDir = public_path('resumes');
        if (!File::exists($resumeDir)) {
            return $this->error('No resumes folder found.', 404);
        }

        $zipFileName = $user->company->name . '_resumes.zip';
        $zipFilePath = public_path($zipFileName);

        $zip = new ZipArchive;
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return $this->error('Failed to create ZIP file.', 500);
        }

        $added = false;
        
        // Get all resume files from the resumes directory
        $resumeFiles = File::files($resumeDir);
        
        if (empty($resumeFiles)) {
            $zip->close();
            File::delete($zipFilePath);
            return $this->error('No resume files found in the resumes folder.', 404);
        }

        // Add all resume files to the ZIP
        foreach ($resumeFiles as $resumeFile) {
            $fileName = $resumeFile->getFilename();
            $filePath = $resumeFile->getPathname();
            
            if (strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) === 'pdf') {
                $zip->addFile($filePath, $fileName);
                $added = true;
            }
        }

        $zip->close();

        if (!$added) {
            File::delete($zipFilePath);
            return $this->error('No resumes found to include.', 404);
        }

        return $this->ok('All resumes zipped successfully.', [
            'download_url' => url($zipFileName),
            'filename' => $zipFileName,
        ]);
    }

    public function updateApplicationStatus(ApplicationUpdateRequest $request, Application $application)
    {
        try 
        {
            $user = auth()->user();

            if(!$user->isEmployer() || !$user->company()) {
                return $this->error('You must be an employer who has a company to update an application status', 401);
            }

            if (!$application->vacancy || $application->vacancy->company_id !== $user->company->id) {
                return $this->error('You are not authorized to modify this application.', 403);
            }

            $attributes = $request->mappedAttributes();

            $application->update([
                'status' => $attributes['status']
            ]);

            return $this->ok('Application status updated successfully.', [
                'application_id' => $application->id,
                'applicant_name' => $application->user->name,
                'new_status' => $application->status,
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Something went wrong while updating the application status.',
                'error' => $e->getMessage(),
            ], 500);
        }

    }

}