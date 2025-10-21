<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users_vacancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // job seeker
            $table->foreignId('vacancy_id')->constrained('vacancies')->onDelete('cascade');
            $table->timestamps();

            // prevent duplicate pivot rows: a user should only have one users_vacancy row per vacancy
            $table->unique(['user_id', 'vacancy_id'], 'users_vacancy_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users_vacancies');
    }
};
