<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class VacancyFilter extends QueryFilter
{
    // Search by keyword in title or description
    public function search($keyword)
    {
        return $this->query->where(function (Builder $query) use ($keyword) {
            $query->where('title', 'LIKE', "%{$keyword}%")
                  ->orWhere('description', 'LIKE', "%{$keyword}%");
        });
    }

    // Filter by location
    public function location($location)
    {
        return $this->query->where('location', 'LIKE', "%{$location}%");
    }

    // Filter by job type
    public function job_type($jobType)
    {
        return $this->query->where('job_type', $jobType);
    }

    // Filter by vacancy type
    public function vacancy_type($vacancyType)
    {
        return $this->query->where('job_type', $vacancyType);
    }
}
