<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('employer_id')->constrained('users')->onDelete('cascade'); // who posted it
            $table->string('title');
            $table->text('description');
            $table->string('location')->nullable();
            $table->enum('job_type', ['full_time','part_time','contract','remote'])->default('full_time');
            $table->unsignedInteger('salary_min')->nullable();
            $table->unsignedInteger('salary_max')->nullable();
            $table->boolean('is_filled')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('vacancies', function (Blueprint $table) {
            $table->index(['title']);
            $table->index(['location']);
            $table->index(['job_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacancies');
    }
};
