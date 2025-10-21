<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $jobSeekerRole = Role::firstOrCreate(['name' => 'job_seeker']);
        $employerRole = Role::firstOrCreate(['name' => 'employer']);

        // Admin user
        User::create([
            'role_id' => $adminRole->id,
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '01000000000',
            'password' => Hash::make('password'),
        ]);

        // 25 job seekers
        User::factory()->count(25)->create([
            'role_id' => $jobSeekerRole->id,
        ]);

        // 25 employers
        User::factory()->count(25)->create([
            'role_id' => $employerRole->id,
        ]);
    }
}