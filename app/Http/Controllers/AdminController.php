<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
Use App\Models\User;
use App\Models\Vacancy;
use App\Models\Application;
use App\Traits\ApiResponses;

class AdminController extends Controller
{
    use ApiResponses;

    public function dashboard() {

        // Verify that user is an admin
        // View Total Jobs, total users, total appliactions in the system

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
}
