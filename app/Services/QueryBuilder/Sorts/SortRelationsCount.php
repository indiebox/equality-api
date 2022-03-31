<?php

namespace App\Services\QueryBuilder\Sorts;

use App\Services\QueryBuilder\Exceptions\SortQueryException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Spatie\QueryBuilder\QueryBuilderRequest;
use Spatie\QueryBuilder\Sorts\Sort;

class SortRelationsCount implements Sort
{
    protected $relationName;

    public function __construct($relationName)
    {
        $this->relationName = $relationName;
    }

    public function __invoke(Builder $query, bool $descending, string $property)
    {
        $fullName = $this->relationName . config('query-builder.count_suffix');

        if (!app(QueryBuilderRequest::class)->includes()->contains($fullName)) {
            throw new SortQueryException(
                Response::HTTP_BAD_REQUEST,
                "Sort by '{$this->relationName}' count allowed only with including '{$fullName}' in query."
            );
        }

        $query->orderBy($property, $descending ? 'desc' : 'asc');
    }
}
