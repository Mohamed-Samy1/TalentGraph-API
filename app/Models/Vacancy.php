<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Http\Filters\VacancyFilter;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Vacancy extends Model
{
    use HasFactory;

    use LogsActivity;

    protected static $logName = 'vacancy';
    protected static $logAttributes = ['title', 'description', 'company_id'];
    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('vacancy')
            ->logOnly(['title', 'description', 'company_id', 'is_filled', 'status'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Vacancy was {$eventName}");
    }

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
        'status',
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

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function scopeFilter($query, VacancyFilter $filters)
    {
        return $filters->apply($query);
    }
}
