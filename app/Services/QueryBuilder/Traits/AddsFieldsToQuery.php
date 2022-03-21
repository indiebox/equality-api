<?php

namespace App\Services\QueryBuilder\Traits;

use Illuminate\Support\Str;
use Spatie\QueryBuilder\Concerns\AddsFieldsToQuery as ConcernsAddsFieldsToQuery;

trait AddsFieldsToQuery
{
    use ConcernsAddsFieldsToQuery {
        allowedFields as private parentAllowedFields;
    }

    protected $defaultFields;

    public function allowedFields($fields, $defaultFields = []): self
    {
        $this->defaultFields = collect($defaultFields);

        $this->parentAllowedFields($fields);

        return $this;
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
