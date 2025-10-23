<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
Use App\Models\User;
use App\Models\Vacancy;
use App\Models\Application;
use App\Traits\ApiResponses;
use Illuminate\Database\Eloquent\SoftDeletes;

use Spatie\Activitylog\Models\Activity;


class AdminController extends Controller
{
    use ApiResponses;
    use SoftDeletes;

    public function dashboard() {

        $user = auth()->user();

        if (!$user->isAdmin()) {
            return $this->error("Only admins can view the dashboard", 401);
        }

        $totalUsers = User::count();
        $totalVacancies = Vacancy::count();
        $totalApplications = Application::count();


        return $this->ok("Users count calculated successfully", [
            "Users Count:" => $totalUsers,
            "Vacancies Available:" => $totalVacancies,
            "Applications Submitted:" => $totalApplications
        ], 200);
    }

    public function deleteVacancy(Vacancy $vacancy) {
        
        $user = auth()->user();

        if (!$user->isAdmin()) {
            return $this->error("Only admins and vacancy publishers can delete vacancies", 401);
        }

        $vacancy->delete();

        return $this->ok("Vacancy deleted successfully", 200);
    }

    public function activateDeactivatePosting() {

    }

    public function viewActivityLogs()
    {
        $user = auth()->user();

        if (!$user->isAdmin()) {
            return $this->error("Only admins can view activity logs", 401);
        }

        $logs = Activity::with(['causer:id,name,email'])
            ->latest()
            ->take(100)
            ->get(['id', 'log_name', 'description', 'created_at', 'causer_id', 'causer_type', 'properties']);

        return $this->ok("Activity logs fetched successfully", ['logs' => $logs]);
    }


}
