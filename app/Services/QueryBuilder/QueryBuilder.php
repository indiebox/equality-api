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
    use AddsFieldsToQuery;

    public $subjectIsModel = false;

    public $freshQuery = null;

    public function get()
    {
        if ($this->subjectIsModel) {
            return $this->subject;
        }

        return $this->__call('get', func_get_args());
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

    #region AddsIncludesToQuery trait

    public function allowedIncludes($includes): self
    {
        return $this->traitAllowedIncludes($includes);
    }

    #endregion

    #region SortsQuery trait

    public function allowedSorts($sorts): self
    {
        if ($this->subjectIsModel) {
            throw new LogicException("Method 'allowedSorts' cant be used with loaded model.");
        }

        return parent::allowedSorts($sorts);
    }

    #endregion

    #region FiltersQuery trait

    public function allowedFilters($filters): self
    {
        if ($this->subjectIsModel) {
            throw new LogicException("Method 'allowedFilters' cant be used with loaded model.");
        }

        return parent::allowedFilters($filters);
    }

    #endregion
}
