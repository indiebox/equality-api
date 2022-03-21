<?php

namespace App\Services\QueryBuilder\Traits;

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

    /**
     * Apply requested fields to result.
     * This method should be called after `->get()`.
     * All unrequested fields will be hidden in result.
     * If not fields requested, default fields would be applied.
     * @param mixed $result
     */
    protected function applyFieldsToResult($result)
    {
        $request = $this->request;
        $models = is_iterable($result) ? $result : [$result];
        $tableName = $this->getModel()->getTable();

        $modelFields = $this->defaultFields
            ->reduce(function ($result, $value) use ($tableName) {
                $value = explode(".", $value);
                $key = null;

                if (count($value) == 1 || ($value[0] == $tableName && count($value) == 2)) {
                    $key = $tableName;
                    $field = end($value);
                } else {
                    $field = array_pop($value);
                    $key = implode(".", $value);
                }

                $result[$key] = $result[$key] ?? collect();
                $result[$key]->add($field);

                return $result;
            }, collect())
            ->merge($this->request->fields());

        /**
         * Apply fields to nested relations.
         * @param mixed $relation Model realation (for example, `projects`, `teams`, etc.)
         * @param array $fields Fields to apply (for example, `id`, `name`, etc.)
         * @param array $nestedRelation Array of nested relation (like `[projects, leader] => projects.leader`, etc.).
         */
        function applyNested($relation, $fields, $nestedRelation, $request)
        {
            $next = next($nestedRelation);
            $relation = is_iterable($relation) ? $relation : [$relation];

            foreach ($relation as $item) {
                if ($next === false) {
                    $attributes = collect($item->getAttributes())->except($request->includes()->merge($fields))->keys()->toArray();

                    $item->makeHidden($attributes);
                } else {
                    if (!$item->relationLoaded($next)) {
                        break;
                    }

                    applyNested($item->{$next}, $fields, $nestedRelation, $request);
                }
            }
        }

        foreach ($models as $model) {
            foreach ($modelFields as $relation => $fields) {
                $nestedRelation = explode(".", $relation);

                // Fields for current model itself (not nested).
                if ($nestedRelation[0] == $tableName) {
                    applyNested($model, $fields, [], $request);
                    continue;
                }

                if (!$model->relationLoaded($nestedRelation[0])) {
                    continue;
                }

                // Fields for nested models.
                applyNested($model->{$nestedRelation[0]}, $fields, $nestedRelation, $request);
            }
        }
    }

    protected function addRequestedModelFieldsToQuery()
    {
        // We dont need this method, because we apply fields after extraction
        // results from database, so all foreign keys (and relations)
        // will be loaded correctly.
    }
}
