<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'users_vacancy_id',
        'resume_path',
        'resume_size',
        'resume_name',
        'cover_letter',
        'status',
        'withdrawn',
        'applied_at',
    ];

    protected $casts = [
        'withdrawn' => 'boolean',
        'applied_at' => 'datetime',
    ];

    public function usersVacancy(): BelongsTo
    {
        return $this->belongsTo(UsersVacancy::class, 'users_vacancy_id');
    }

    // convenience: applicant via usersVacancy
    public function applicant()
    {
        return $this->hasOneThrough(
            User::class,
            UsersVacancy::class,
            'id',          // Foreign key on UsersVacancy table...
            'id',          // Foreign key on Users table...
            'users_vacancy_id', // Local key on Applications (this model) -> users_vacancies.id
            'user_id'      // Local key on UsersVacancy -> users.id
        );
    }

    // convenience: vacancy via usersVacancy
    public function vacancy()
    {
        return $this->hasOneThrough(
            Vacancy::class,
            UsersVacancy::class,
            'id',          // FK on UsersVacancy
            'id',          // FK on Vacancies
            'users_vacancy_id', // local key on applications
            'vacancy_id'
        );
    }
}
