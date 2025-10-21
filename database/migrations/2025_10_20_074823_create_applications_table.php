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

            // normalized: application belongs to a users_vacancies pivot row
            $table->foreignId('users_vacancy_id')->constrained('users_vacancies')->onDelete('cascade');

            // resume and cover
            $table->string('resume_path');
            $table->integer('resume_size')->nullable();
            $table->string('resume_name')->nullable();
            $table->text('cover_letter')->nullable();

            $table->enum('status', ['pending','reviewed','accepted','rejected'])->default('pending');
            $table->boolean('withdrawn')->default(false);

            $table->timestamp('applied_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
