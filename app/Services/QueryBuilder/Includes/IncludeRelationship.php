<?php

namespace App\Services\QueryBuilder\Includes;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Includes\IncludedRelationship;

class IncludeRelationship extends IncludedRelationship
{
    public function __invoke(Builder $query, string $relationship)
    {
        $relatedTables = collect(explode('.', $relationship));

        $withs = $relatedTables
            ->mapWithKeys(function ($table, $key) use ($query, $relatedTables) {
                return [$relatedTables->slice(0, $key + 1)->implode('.')];
            })
            ->toArray();

        $query->with($withs);
    }
}
