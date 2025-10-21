<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class VacancyFilter extends QueryFilter
{
    // Search by keyword in title or description
    public function search($keyword)
    {
        // Sanitize and validate input
        $keyword = $this->sanitizeInput($keyword);
        
        if (empty($keyword)) {
            return $this->query;
        }

        return $this->query->where(function (Builder $query) use ($keyword) {
            $query->where('title', 'LIKE', "%{$keyword}%")
                  ->orWhere('description', 'LIKE', "%{$keyword}%");
        });
    }

    // Filter by location
    public function location($location)
    {
        $location = $this->sanitizeInput($location);
        
        if (empty($location)) {
            return $this->query;
        }

        return $this->query->where('location', 'LIKE', "%{$location}%");
    }

    // Filter by job type
    public function job_type($jobType)
    {
        $allowedTypes = ['full_time', 'part_time', 'contract', 'remote'];
        
        if (!in_array($jobType, $allowedTypes)) {
            return $this->query;
        }

        return $this->query->where('job_type', $jobType);
    }

    // Filter by vacancy type
    public function vacancy_type($vacancyType)
    {
        $allowedTypes = ['full_time', 'part_time', 'contract', 'remote'];
        
        if (!in_array($vacancyType, $allowedTypes)) {
            return $this->query;
        }

        return $this->query->where('job_type', $vacancyType);
    }

    private function sanitizeInput($input)
    {
        if (empty($input)) {
            return '';
        }

        // Remove potentially dangerous characters
        $input = trim($input);
        $input = strip_tags($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        
        // Limit length to prevent DoS attacks
        $input = substr($input, 0, 255);
        
        return $input;
    }
}
