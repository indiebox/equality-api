<?php

namespace App\Services\QueryBuilder\Traits;

use Spatie\QueryBuilder\Concerns\AddsFieldsToQuery as ConcernsAddsFieldsToQuery;

trait AddsFieldsToQuery
{
    use ConcernsAddsFieldsToQuery {
        allowedFields as parentAllowedFields;
    }

    protected $defaultFields = [];

    public function allowedFields($fields, $defaultFields = []): self
    {
        $this->defaultFields = $defaultFields;

        $this->parentAllowedFields($fields);

        return $this;
    }

    protected function addRequestedModelFieldsToQuery()
    {
        $modelTableName = $this->getModel()->getTable();

        $modelFields = $this->request->fields()->get($modelTableName);

        if (empty($modelFields)) {
            $modelFields = $this->defaultFields;
        }

        $prependedFields = $this->prependFieldsWithTableName($modelFields, $modelTableName);

        if ($this->subjectIsModel) {
            $this->makeHidden(collect(array_keys($this->getAttributes()))->diff($modelFields)->toArray());
        } else {
            $this->select($prependedFields);
        }
    }
}
