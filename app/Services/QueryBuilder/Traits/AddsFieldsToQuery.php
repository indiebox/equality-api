<?php

namespace App\Services\QueryBuilder\Traits;

use Illuminate\Support\Str;
use Spatie\QueryBuilder\Concerns\AddsFieldsToQuery as ConcernsAddsFieldsToQuery;

trait AddsFieldsToQuery
{
    use ConcernsAddsFieldsToQuery {
        allowedFields as private parentAllowedFields;
        getRequestedFieldsForRelatedTable as private parentGetRequestedFieldsForRelatedTable;
    }

    protected $defaultFields;

    public function allowedFields($fields, $defaultFields = []): self
    {
        $this->defaultFields = collect($defaultFields);

        $this->parentAllowedFields($fields);

        return $this;
    }

    public function getRequestedFieldsForRelatedTable(string $relation): array
    {
        $fields = $this->parentGetRequestedFieldsForRelatedTable($relation);

        if (empty($fields) && !empty($this->defaultFields)) {
            $table = Str::plural(Str::snake($relation));

            $fields = $this->defaultFields->filter(fn($value) => str_starts_with($value, $table));
            $fields = $fields->map(fn($value) => explode(".", $value, 2)[1])->toArray();
        }

        // Force add relevant foreign key column for Eloquent to work correctly.
        $model = $this->subject;
        if (method_exists($model, $relation) && method_exists($relation = $model->{$relation}(), 'getForeignKeyName')) {
            $fields[] = $relation->getForeignKeyName();
        }

        return $fields;
    }

    protected function addRequestedModelFieldsToQuery()
    {
        $modelTableName = $this->getModel()->getTable();

        $modelFields = $this->request->fields()->get($modelTableName);

        if (empty($modelFields)) {
            $modelFields = $this->defaultFields->filter(function ($value) use ($modelTableName) {
                return $value == $modelTableName
                    || !str_contains($value, ".");
            });
        }

        $prependedFields = $this->prependFieldsWithTableName($modelFields->toArray(), $modelTableName);

        if ($this->subjectIsModel) {
            $this->makeHidden(collect(array_keys($this->getAttributes()))->diff($modelFields)->toArray());
        } else {
            $this->select($prependedFields);
        }
    }
}
