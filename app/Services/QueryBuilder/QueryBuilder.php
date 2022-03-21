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

        #region Setup fields for defaults relations
        $modelFields = $this->request->fields();
        $includeRelations = $this->request->includes();
        $models = is_iterable($result) ? $result : [$result];

        foreach ($models as $model) {
            $relations = collect($model->getRelations())->except($includeRelations);

            foreach ($relations as $relationName => $relation) {
                $fields = $modelFields->get($relationName);

                if (empty($fields)) {
                    $fields = $this->defaultFields->filter(fn($value) => str_starts_with($value, $relationName))
                    ->map(fn($value) => explode(".", $value, 2)[1])
                    ->toArray();
                }

                $relation = is_iterable($relation) ? $relation : [$relation];

                foreach ($relation as $relationItem) {
                    $diff = collect(array_keys($relationItem->getAttributes()))->diff($fields)->toArray();
                    $relationItem->makeHidden($diff);
                }
            }
        }
        #endregion

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
