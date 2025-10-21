<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vacancy extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'employer_id',
        'title',
        'description',
        'location',
        'job_type',
        'salary_min',
        'salary_max',
        'is_filled',
    ];

    protected $casts = [
        'is_filled' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employer_id');
    }

    public function usersVacancies(): HasMany
    {
        return $this->hasMany(UsersVacancy::class);
    }

    // convenience: get applications through users_vacancies
    public function applications()
    {
        return $this->hasManyThrough(
            Application::class,
            UsersVacancy::class,
            'vacancy_id',        // FK on users_vacancies
            'users_vacancy_id',  // FK on applications
            'id',                // local key on vacancies
            'id'                 // local key on users_vacancies
        );
    }
}
