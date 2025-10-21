<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UsersVacancy extends Model
{
    use HasFactory;

    protected $table = 'users_vacancies';

    protected $fillable = [
        'user_id',
        'vacancy_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'users_vacancy_id');
    }
}
