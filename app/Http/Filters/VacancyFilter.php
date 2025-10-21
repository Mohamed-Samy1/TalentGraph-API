<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class VacancyFilter extends QueryFilter
{
    public function search($keyword)
    {
        $keyword = $this->sanitizeInput($keyword);
        
        if (empty($keyword)) {
            return $this->query;
        }

        return $this->query->where(function (Builder $query) use ($keyword) {
            $query->where('title', 'LIKE', "%{$keyword}%")
                  ->orWhere('description', 'LIKE', "%{$keyword}%");
        });
    }

    public function location($location)
    {
        $location = $this->sanitizeInput($location);
        
        if (empty($location)) {
            return $this->query;
        }

        return $this->query->where('location', 'LIKE', "%{$location}%");
    }

    public function job_type($jobType)
    {
        $allowedTypes = ['full_time', 'part_time', 'contract', 'remote'];
        
        if (!in_array($jobType, $allowedTypes)) {
            return $this->query;
        }

        return $this->query->where('job_type', $jobType);
    }

    public function vacancy_type($vacancyType)
    {
        $allowedTypes = ['full_time', 'part_time', 'contract', 'remote'];
        
        if (!in_array($vacancyType, $allowedTypes)) {
            return $this->query;
        }

        return $this->query->where('job_type', $vacancyType);
    }

    public function sort($value = null)
    {
        $direction = 'desc';

        if (!empty($value)) {
            $value = strtolower($value);
            if ($value === 'oldest') {
                $direction = 'asc';
            }
        }

        return $this->query->orderBy('created_at', $direction);
    }

    private function sanitizeInput($input)
    {
        if (empty($input)) {
            return '';
        }

        // Remove potentially dangerous characters
        $input = trim($input);
        $input = strip_tags($input);
        $input = htmlspecialchars($input);
        
        return $input;
    }
}
