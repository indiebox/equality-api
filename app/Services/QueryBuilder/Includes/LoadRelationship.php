<?php

namespace App\Services\QueryBuilder\Includes;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Includes\IncludedRelationship;

class LoadRelationship extends IncludedRelationship
{
    protected $builder;

    public function __construct($builder)
    {
        $this->builder = $builder;
    }

    public function __invoke(Builder $query, string $relationship)
    {
        $relatedTables = collect(explode('.', $relationship));

        $loads = $relatedTables
            ->mapWithKeys(function ($table, $key) use ($query, $relatedTables) {
                return [$relatedTables->slice(0, $key + 1)->implode('.')];
            })
            ->toArray();

        foreach ($loads as $value) {
            $this->builder->loadRelations[] = $value;
        }
    }
}
