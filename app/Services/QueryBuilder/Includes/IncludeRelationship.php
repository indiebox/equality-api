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
                $fullRelationName = $relatedTables->slice(0, $key + 1)->implode('.');

                if ($this->getRequestedFieldsForRelatedTable) {
                    $fields = ($this->getRequestedFieldsForRelatedTable)($fullRelationName);
                }

                if (empty($fields)) {
                    return [$fullRelationName];
                }

                return [
                    $fullRelationName => function ($query) use ($fields) {
                        $query->select($query->qualifyColumns($fields));
                    },
                ];
            })
            ->toArray();

        $query->with($withs);
    }
}
