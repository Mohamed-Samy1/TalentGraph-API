<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplicationStoreRequest;
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

    // Job Seeker methods for managing applications

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

    // Employer methods for managing applications

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

        $zipFileName = 'company_' . $user->company->id . '_resumes_' . time() . '.zip';
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
            
            // Only add PDF files (you can modify this to include other file types)
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

        // Return a single download link
        return $this->ok('All resumes zipped successfully.', [
            'download_url' => url($zipFileName),
            'filename' => $zipFileName,
        ]);
    }
}



/* 
    return [
        'id' => $application->id,
        'vacancy_title' => $application->vacancy->title,
        'vacancy_id' => $application->vacancy->id,
        'applicant_name' => $application->applicant->name,
        'applicant_email' => $application->applicant->email,
        'applicant_phone' => $application->applicant->phone,
        'status' => $application->status,
        'applied_at' => $application->applied_at,
        'resume_name' => $application->resume_name,
        'resume_path' => $application->resume_path,
        'resume_size' => $application->resume_size,
        'cover_letter' => $application->cover_letter,
    ];

*/