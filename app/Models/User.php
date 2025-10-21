<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // if using sanctum
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'role_id',
        'name',
        'email',
        'phone',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // relations
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    // employer has one company (one-to-one)
    public function company(): HasOne
    {
        return $this->hasOne(Company::class, 'employer_id');
    }

    // vacancies posted by this user (employer)
    public function postedVacancies(): HasMany
    {
        return $this->hasMany(Vacancy::class, 'employer_id');
    }

    // pivot relationships: applications via users_vacancies pivot
    public function usersVacancies(): HasMany
    {
        return $this->hasMany(UsersVacancy::class, 'user_id');
    }

    public function applications()
    {
        // convenience: has many through UsersVacancy -> Application
        return $this->hasManyThrough(
            Application::class,
            UsersVacancy::class,
            'user_id',           // FK on UsersVacancy table...
            'users_vacancy_id',  // FK on Applications table...
            'id',                // Local key on Users (users.id)
            'id'                 // Local key on UsersVacancy (users_vacancies.id)
        );
    }

    // helper checks
    public function isEmployer(): bool
    {
        return $this->role && $this->role->name === 'employer';
    }

    public function isJobSeeker(): bool
    {
        return $this->role && $this->role->name === 'job_seeker';
    }

    public function isAdmin(): bool
    {
        return $this->role && $this->role->name === 'admin';
    }
}
