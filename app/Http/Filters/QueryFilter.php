<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

abstract class QueryFilter
{
    protected $query;
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

     public function apply(Builder $query)
    {
        $this->query = $query;

        // Apply all filters dynamically
        foreach ($this->filters() as $name => $value) {
            if (method_exists($this, $name)) {
                if (!is_null($value) && $value !== '') {
                    $this->$name($value);
                }
            }
        }

        // Always apply default sorting if sort() method exists
        if (method_exists($this, 'sort')) {
            $sortValue = $this->request->get('sort'); // may be null
            $this->sort($sortValue);
        }

        return $this->query;
    }

    public function filters()
    {
        return $this->request->all();
    }
}
