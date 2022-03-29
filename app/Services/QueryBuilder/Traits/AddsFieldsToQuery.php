<?php

namespace App\Services\QueryBuilder\Traits;

use App\Services\QueryBuilder\Contracts\ResourceWithFields;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use InvalidArgumentException;
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

    /**
     * Setup allowed and default fields.
     *
     * This method should be called before `allowedIncludes`.
     * Default fields will be automatically added to allowed fields.
     *
     * @param array $fields Allowed fields.
     * @param array $defaultFields Default fields, if no any requested.
     * @param string|null $defaultName The default name for model itself.
     * @return self
     */
    public function allowedFields($fields, $defaultFields = [], $defaultName = null): self
    {
        if ($this->allowedIncludes instanceof Collection) {
            throw new AllowedFieldsMustBeCalledBeforeAllowedIncludes();
        }

        if ($this->subjectIsCollection && $this->subject->count() == 0) {
            return $this;
        }

        $this->defaultName = $defaultName ?? $this->getDefaultName();

        $this->defaultFields = $this->parseFields($defaultFields, false);
        $this->allowedFields = $this->parseFields($fields, true)
            ->concat($this->defaultFields)
            ->unique();

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
        if ($this->subjectIsCollection && $result->count() == 0) {
            return;
        }

        $models = is_iterable($result) ? $result : [$result];

        $requestedFields = collect($this->defaultFields)
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

        foreach ($models as $model) {
            foreach ($requestedFields as $relation => $fields) {
                $nestedRelation = explode(".", $relation);
                $hideFields = $this->allowedFields
                    ->filter(fn($value) => str_starts_with($value, $relation))
                    ->map(fn($value) => last(explode(".", $value)))
                    ->diff($fields)
                    ->values()
                    ->toArray();

                // Fields for current model itself (not nested).
                if ($nestedRelation[0] == $this->defaultName) {
                    $this->hideFields($model, $hideFields, []);

                    continue;
                }

                if (!$model->relationLoaded($nestedRelation[0])) {
                    continue;
                }

                // Fields for nested models.
                $this->hideFields($model->{$nestedRelation[0]}, $hideFields, $nestedRelation);
            }
        }
    }

    protected function addRequestedModelFieldsToQuery()
    {
        // We dont need this method, because we apply fields after extraction
        // results from database, so all foreign keys (and relations)
        // will be loaded correctly.
    }

    protected function getDefaultName()
    {
        return $this->subjectIsCollection
            ? $this->subject->first()->getTable()
            : $this->getModel()->getTable();
    }

    /**
     * Parse the given fields.
     * @param array $fields The fields.
     * @param bool $isAllowed Is allowed or default fields.
     * @return \Illuminate\Support\Collection
     */
    private function parseFields($fields, $isAllowed)
    {
        return collect($fields)
            ->map(function ($fieldName, $key) use ($isAllowed) {
                if (is_string($key)) {
                    $class = $key;
                } else {
                    $class = is_string($fieldName) && class_exists($fieldName)
                        ? $fieldName
                        : null;
                }

                if ($class) {
                    if (in_array(ResourceWithFields::class, class_implements($class))) {
                        $prepends = $class::defaultName() ?: '';
                        if ($fieldName != $class) {
                            $prepends = $fieldName;
                        }
                        $prepends = Arr::wrap($prepends);

                        $resultFields = collect();
                        foreach ($prepends as $prepend) {
                            if ($prepend != "") {
                                $prepend .= ".";
                            }

                            $fields = $isAllowed
                                ? $class::allowedFields()
                                : $class::defaultFields();

                            $fields = collect($fields)
                                ->map(function ($field, $key) use ($prepend) {
                                    if (is_string($key)) {
                                        return $this->prependField($prepend . $key, $this->defaultName);
                                    }

                                    return $this->prependField($prepend . $field, $this->defaultName);
                                });

                            $resultFields = $resultFields->concat($fields);
                        }

                        return $resultFields->toArray();
                    } else {
                        throw new InvalidArgumentException("Type of {$class} doesn`t implements ResourceWithFields interface.");
                    }
                }

                return $this->prependField($fieldName, $this->defaultName);
            })
            ->flatten();
    }

    /**
     * Apply requested fields.
     * @param mixed $relation Model relation (for example, `projects`, `teams`, etc.)
     * @param array $fields Fields to apply (for example, `id`, `name`, etc.)
     * @param array $nestedRelation Array of nested relation (like `[projects, leader] => projects.leader`, etc.).
     */
    private function hideFields($relation, $fields, $nestedRelation)
    {
        $next = next($nestedRelation);

        $relation = is_iterable($relation) ? $relation : [$relation];
        if (count($relation) == 0 || $relation[0] == null) {
            return;
        }

        foreach ($relation as $item) {
            if ($next === false) {
                $item->makeHidden($fields);
            } else {
                if (!$item->relationLoaded($next)) {
                    break;
                }

                $this->hideFields($item->{$next}, $fields, $nestedRelation);
            }
        }
    }
}
