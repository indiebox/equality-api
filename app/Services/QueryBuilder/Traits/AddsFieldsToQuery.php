<?php

namespace App\Services\QueryBuilder\Traits;

use Illuminate\Support\Collection;
use Spatie\QueryBuilder\Exceptions\AllowedFieldsMustBeCalledBeforeAllowedIncludes;

trait AddsFieldsToQuery
{
    /**
     * List of default fields that will be applied
     * when no any requested.
     * @var \Illuminate\Support\Collection
     */
    protected $defaultFields;

    /**
     * The default name for parent model.
     * @var string|null
     */
    protected $defaultName;

    public function allowedFields($fields, $defaultFields = [], $defaultName = null): self
    {
        if ($this->allowedIncludes instanceof Collection) {
            throw new AllowedFieldsMustBeCalledBeforeAllowedIncludes();
        }

        $this->defaultName = $defaultName ?? $this->getModel()->getTable();

        $this->allowedFields = collect($fields)
            ->push(...$defaultFields)
            ->map(function (string $fieldName) use ($defaultName) {
                return $this->prependField($fieldName, $defaultName);
            });

        $this->defaultFields = collect($defaultFields);

        $this->ensureAllFieldsExist();

        $this->addRequestedModelFieldsToQuery();

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
        $models = is_iterable($result) ? $result : [$result];

        $modelFields = collect($this->defaultFields)
            ->reduce(function ($result, $value) {
                $value = explode(".", $value);
                $key = null;

                if (count($value) == 1 || ($value[0] == $this->defaultName && count($value) == 2)) {
                    $key = $this->defaultName;
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

        $except = collect($this->allowedIncludes)->map(function ($allowedInclude) {
            return $allowedInclude->getName();
        });

        foreach ($models as $model) {
            foreach ($modelFields as $relation => $fields) {
                $nestedRelation = explode(".", $relation);
                $fields = collect($except)->merge($fields);

                // Fields for current model itself (not nested).
                if ($nestedRelation[0] == $this->defaultName) {
                    $this->applyFields($model, $fields, []);

                    continue;
                }

                if (!$model->relationLoaded($nestedRelation[0])) {
                    continue;
                }

                // Fields for nested models.
                $this->applyFields($model->{$nestedRelation[0]}, $fields, $nestedRelation);
            }
        }
    }

    /**
     * Apply requested fields.
     * @param mixed $relation Model relation (for example, `projects`, `teams`, etc.)
     * @param array $fields Fields to apply (for example, `id`, `name`, etc.)
     * @param array $nestedRelation Array of nested relation (like `[projects, leader] => projects.leader`, etc.).
     */
    private function applyFields($relation, $fields, $nestedRelation)
    {
        $next = next($nestedRelation);

        $relation = is_iterable($relation) ? $relation : [$relation];
        $attributes = collect($relation[0]->getAttributes())
            ->except($fields)
            ->keys()
            ->toArray();

        foreach ($relation as $item) {
            if ($next === false) {
                $item->makeHidden($attributes);
            } else {
                if (!$item->relationLoaded($next)) {
                    break;
                }

                $this->applyFields($item->{$next}, $fields, $nestedRelation);
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
