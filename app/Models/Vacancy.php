<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Http\Filters\VacancyFilter;

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

    public function scopeFilter($query, VacancyFilter $filters)
    {
        return $filters->apply($query);
    }
}
