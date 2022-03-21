<?php

namespace App\Services\QueryBuilder\Traits;

trait AddsFieldsToQuery
{
    protected function addRequestedModelFieldsToQuery()
    {
        $modelTableName = $this->getModel()->getTable();

        $modelFields = $this->request->fields()->get($modelTableName);

        if (empty($modelFields)) {
            return;
        }

        $prependedFields = $this->prependFieldsWithTableName($modelFields, $modelTableName);

        if ($this->subjectIsModel) {
            $this->makeHidden(collect(array_keys($this->getAttributes()))->diff($modelFields)->toArray());
        } else {
            $this->select($prependedFields);
        }
    }
}
