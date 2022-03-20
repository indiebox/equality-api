<?php

namespace App\Services\QueryBuilder\Includes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\Includes\IncludeInterface;

class IncludeCount implements IncludeInterface
{
    protected $builder;

    public function __construct($builder)
    {
        $this->builder = $builder;
    }

    public function __invoke(Builder $query, string $count)
    {
        $this->builder->withCount[] = Str::before($count, config('query-builder.count_suffix'));
    }
}
