<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // admin, employer, job_seeker, guest
            $table->timestamps();
        });

        // seed basic roles
        DB::table('roles')->insert([
            ['name' => 'guest', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'job_seeker', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'employer', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
  