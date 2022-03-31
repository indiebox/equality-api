<?php

namespace App\Services\QueryBuilder\Includes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\Includes\IncludedCount;

class LoadCount extends IncludedCount
{
    protected $builder;

    public function __construct($builder)
    {
        $this->builder = $builder;
    }

    public function __invoke(Builder $query, string $count)
    {
        $this->builder->loadCount[] = Str::before($count, config('query-builder.count_suffix'));
    }
}
