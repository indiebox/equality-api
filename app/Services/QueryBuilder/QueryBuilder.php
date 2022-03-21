<?php

namespace App\Services\QueryBuilder;

use App\Services\QueryBuilder\Traits\AddsFieldsToQuery;
use App\Services\QueryBuilder\Traits\AddsIncludesToQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use LogicException;
use Spatie\QueryBuilder\QueryBuilder as BaseQueryBuilder;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class QueryBuilder extends BaseQueryBuilder
{
    use AddsIncludesToQuery {
        allowedIncludes as traitAllowedIncludes;
    }
    use AddsFieldsToQuery {
        allowedFields as traitAllowedFields;
    }

    public $subjectIsModel = false;

    protected $freshQuery = null;

    public function get()
    {
        if ($this->subjectIsModel) {
            $result = $this->subject;
        } else {
            $result = $this->__call('get', func_get_args());
        }

        $modelFields = $this->defaultFields->reduce(function ($result, $value) {
            $value = explode(".", $value);

            if (count($value) == 1 || ($value[0] == $this->getModel()->getTable() && count($value) == 2)) {
                return $result;
            } else {
                $field = array_pop($value);
                $restoredValue = implode(".", $value);

                $result[$restoredValue] = $result[$restoredValue] ?? collect();
                $result[$restoredValue]->add($field);

                return $result;
            }
        }, collect())->merge($this->request->fields()->except($this->getModel()->getTable()));

        $models = is_iterable($result) ? $result : [$result];

        // Relation - relation from model(`projects`, `members`, etc.)
        // Fields - field for this nested relation(`id`, `name`, etc.)
        // NestedRelation - `projects.leader`, etc.
        function throughNestedRelation($relation, $fields, $nestedRelation)
        {
            $next = next($nestedRelation);
            $relation = is_iterable($relation) ? $relation : [$relation];

            foreach ($relation as $item) {
                if ($next === false) {
                    $diff = collect(array_keys($item->getAttributes()))->diff($fields)->toArray();
                    $item->makeHidden($diff);
                } else {
                    if (!$item->relationLoaded($next)) {
                        break;
                    }

                    throughNestedRelation($item->{$next}, $fields, $nestedRelation);
                }
            }
        }

        foreach ($models as $model) {
            foreach ($modelFields as $relation => $fields) {
                $nestedRelation = explode(".", $relation);

                if (!$model->relationLoaded($nestedRelation[0])) {
                    continue;
                }

                $currentRelation = $model->{$nestedRelation[0]};
                throughNestedRelation($currentRelation, $fields, $nestedRelation);
            }
        }

        return $result;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation|\Illuminate\Database\Eloquent\Model $subject
     *
     * @return $this
     */
    protected function initializeSubject($subject): self
    {
        if ($subject instanceof Model) {
            $this->subjectIsModel = true;
            $this->subject = $subject;

            return $this;
        }

        return parent::initializeSubject($subject);
    }

    public function getEloquentBuilder(): Builder
    {
        if ($this->subjectIsModel) {
            return $this->freshQuery ?? $this->freshQuery = $this->subject->newQueryWithoutRelationships();
        }

        return parent::getEloquentBuilder();
    }

    /**
     * @param EloquentBuilder|Relation|Model|string $subject
     * @param Request|null $request
     *
     * @return static
     */
    public static function for($subject, ?Request $request = null): self
    {
        return new static($subject, $request);
    }

    #region AddsFieldsToQuery trait

    public function allowedFields($fields, $defaultFields = []): self
    {
        return $this->traitAllowedFields($fields, $defaultFields);
    }

    #endregion

    #region AddsIncludesToQuery trait

    public function allowedIncludes($includes): self
    {
        return $this->traitAllowedIncludes(...func_get_args());
    }

    #endregion

    #region SortsQuery trait

    public function allowedSorts($sorts): self
    {
        if ($this->subjectIsModel) {
            throw new LogicException("Method 'allowedSorts' cant be used with loaded model.");
        }

        return parent::allowedSorts(...func_get_args());
    }

    #endregion

    #region FiltersQuery trait

    public function allowedFilters($filters): self
    {
        if ($this->subjectIsModel) {
            throw new LogicException("Method 'allowedFilters' cant be used with loaded model.");
        }

        if ($this->request->filters()->isEmpty()) {
            // We haven't got any requested filters. No need to parse allowed filters.

            return $this;
        }

        return parent::allowedFilters(...func_get_args());
    }

    #endregion
}
