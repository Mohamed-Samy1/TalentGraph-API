<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();

            // application belongs to a user
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // application belongs to a vacancy
            $table->foreignId('vacancy_id')->constrained('vacancies')->onDelete('cascade');

            // resume and cover
            $table->string('resume_path');
            $table->integer('resume_size')->nullable();
            $table->string('resume_name')->nullable();
            $table->text('cover_letter')->nullable();

            $table->enum('status', ['pending','reviewed','accepted','rejected'])->default('pending');
            $table->boolean('withdrawn')->default(false);

            $table->timestamp('applied_at')->useCurrent();
            $table->timestamps();

            // prevent duplicate applications: a user can only apply once per vacancy
            $table->unique(['user_id', 'vacancy_id'], 'user_vacancy_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
