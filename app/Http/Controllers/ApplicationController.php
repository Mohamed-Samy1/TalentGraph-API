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

    public function downloadApplications() {
        $user = auth()->user();

        if (!$user->isEmployer()) {
            return $this->error('Only employers can download company applications', 403);
        }

        if (!$user->company) {
            return $this->error('You must have a company to download its applications', 403);
        }

        $applications = Application::whereHas('vacancy', function ($query) use ($user) {
            $query->where('company_id', $user->company->id);
        })
        ->where('withdrawn', false)
        ->with(['user', 'vacancy'])
        ->get();
        
        if ($applications->isEmpty()) {
            return $this->error('No applications found for your company', 404);
        }

        // Create downloadedResumes directory if it doesn't exist
        $downloadDir = public_path('downloadedResumes');
        if (!file_exists($downloadDir)) {
            mkdir($downloadDir, 0755, true);
        }

        // Create a ZIP file
        $zipFileName = 'resumes_' . $user->company->id . '_' . time() . '.zip';
        $zipFilePath = $downloadDir . '/' . $zipFileName;

        $zip = new ZipArchive;
        $result = $zip->open($zipFilePath, ZipArchive::CREATE);
        
        if ($result !== true) {
            return $this->error('Failed to create zip file. Error code: ' . $result, 500);
        }

        $added = false;
        $debugInfo = [];
        
        foreach ($applications as $application) {
            if ($application->resume_path) {
                // Handle both relative and absolute paths
                $filePath = $application->resume_path;
                $originalPath = $filePath;
                
                if (!file_exists($filePath)) {
                    // Try with base_path for relative paths
                    $filePath = base_path($application->resume_path);
                }
                
                $debugInfo[] = [
                    'app_id' => $application->id,
                    'original_path' => $originalPath,
                    'resolved_path' => $filePath,
                    'exists' => file_exists($filePath),
                    'size' => file_exists($filePath) ? filesize($filePath) : 0
                ];
                
                if (file_exists($filePath)) {
                    // Get file extension
                    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                    
                    // Create a unique filename with applicant name and vacancy title
                    $applicantName = str_replace(' ', '_', $application->user->name);
                    $vacancyTitle = str_replace(' ', '_', $application->vacancy->title);
                    $localName = $applicantName . '_' . $vacancyTitle . '_' . $application->id . '.' . $extension;
                    
                    // Clean filename for filesystem
                    $localName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $localName);
                    
                    if ($zip->addFile($filePath, $localName)) {
                        $added = true;
                    }
                }
            }
        }
        
        $zip->close();

        if (!$added) {
            if (file_exists($zipFilePath)) {
                @unlink($zipFilePath);
            }
            return $this->error('No resumes available to download. Debug: ' . json_encode($debugInfo), 404);
        }

        // Check if ZIP file was created and has content
        if (!file_exists($zipFilePath) || filesize($zipFilePath) < 100) {
            if (file_exists($zipFilePath)) {
                @unlink($zipFilePath);
            }
            return $this->error('ZIP file is empty or corrupted. Debug: ' . json_encode($debugInfo), 500);
        }

        // Instead of forcing download, return a JSON response with download link
        return response()->json([
            'success' => true,
            'message' => 'ZIP file created successfully',
            'download_url' => url('downloadedResumes/' . $zipFileName),
            'filename' => $zipFileName,
            'file_size' => filesize($zipFilePath),
            'files_count' => $applications->count(),
            'debug_info' => $debugInfo
        ], 200);
    }

    public function downloadApplicationsDirect() {
        $user = auth()->user();

        if (!$user->isEmployer()) {
            return $this->error('Only employers can download company applications', 403);
        }

        if (!$user->company) {
            return $this->error('You must have a company to download its applications', 403);
        }

        $applications = Application::whereHas('vacancy', function ($query) use ($user) {
            $query->where('company_id', $user->company->id);
        })
        ->where('withdrawn', false)
        ->with(['user', 'vacancy'])
        ->get();
        
        if ($applications->isEmpty()) {
            return $this->error('No applications found for your company', 404);
        }

        // Create downloadedResumes directory if it doesn't exist
        $downloadDir = public_path('downloadedResumes');
        if (!file_exists($downloadDir)) {
            mkdir($downloadDir, 0755, true);
        }

        // Create a ZIP file
        $zipFileName = 'resumes_' . $user->company->id . '_' . time() . '.zip';
        $zipFilePath = $downloadDir . '/' . $zipFileName;

        $zip = new ZipArchive;
        $result = $zip->open($zipFilePath, ZipArchive::CREATE);
        
        if ($result !== true) {
            return $this->error('Failed to create zip file. Error code: ' . $result, 500);
        }

        $added = false;
        
        foreach ($applications as $application) {
            if ($application->resume_path) {
                // Handle both relative and absolute paths
                $filePath = $application->resume_path;
                
                if (!file_exists($filePath)) {
                    $filePath = base_path($application->resume_path);
                }
                
                if (file_exists($filePath)) {
                    // Get file extension
                    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                    
                    // Create a unique filename with applicant name and vacancy title
                    $applicantName = str_replace(' ', '_', $application->user->name);
                    $vacancyTitle = str_replace(' ', '_', $application->vacancy->title);
                    $localName = $applicantName . '_' . $vacancyTitle . '_' . $application->id . '.' . $extension;
                    
                    // Clean filename for filesystem
                    $localName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $localName);
                    
                    if ($zip->addFile($filePath, $localName)) {
                        $added = true;
                    }
                }
            }
        }
        
        $zip->close();

        if (!$added) {
            if (file_exists($zipFilePath)) {
                @unlink($zipFilePath);
            }
            return $this->error('No resumes available to download', 404);
        }

        // Check if ZIP file was created and has content
        if (!file_exists($zipFilePath) || filesize($zipFilePath) < 100) {
            if (file_exists($zipFilePath)) {
                @unlink($zipFilePath);
            }
            return $this->error('ZIP file is empty or corrupted', 500);
        }

        // Return the file for download with proper headers
        return response()->download($zipFilePath, $zipFileName, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="' . $zipFileName . '"',
        ])->deleteFileAfterSend(true);
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