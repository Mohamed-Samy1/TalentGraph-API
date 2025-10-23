<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\Application;
use App\Traits\ApiResponses;
use Illuminate\Database\Eloquent\SoftDeletes;

use Spatie\Activitylog\Models\Activity;


class AdminController extends Controller
{
    use ApiResponses;

    public function dashboard() {

        try {
            $user = auth()->user();

            if (!$user->isAdmin()) {
                return $this->error("Only admins can view the dashboard", 403);
            }

            $totalUsers = User::count();
            $totalVacancies = Vacancy::count();
            $totalApplications = Application::count();

            return $this->ok("Dashboard was loaded successfully", [
                "users_count:" => $totalUsers,
                "vacancies_available:" => $totalVacancies,
                "applications_submitted:" => $totalApplications
            ], 200);

        } catch (\Throwable $e) {
            return $this->error('Could not get the dashboard statistics', 500);
        }
    }

    public function deleteVacancy(Vacancy $vacancy) {
        
        try {

            $user = auth()->user();

            if (!$user->isAdmin()) {
                return $this->error("Only admins and vacancy publishers can delete vacancies", 403);
            }

            $vacancy->delete();

            return $this->ok("Vacancy deleted successfully", 200);

        } catch (\Throwable $e) {
            return $this->error('Could not delete the vacancy', 500);
        }
    }

    public function activateDeactivatePosting() {

    }

    public function viewActivityLogs()
    {
        try {

            $user = auth()->user();

            if (!$user->isAdmin()) {
                return $this->error("Only admins can view activity logs", 403);
            }

            $logs = Activity::with(['causer:id,name,email'])
                ->latest()
                ->take(100)
                ->get(['id', 'log_name', 'description', 'created_at', 'causer_id', 'causer_type', 'properties']);

            return $this->ok("Activity logs fetched successfully", ['logs' => $logs]);
        
        } catch (\Throwable $e) {
            return $this->error('Could not fetch the activity log', 500);
        }
    }
}
