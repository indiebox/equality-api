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

        foreach ($loads as $key => $value) {
            $this->builder->loadRelations[$key] = $value;
        }
    }
}
