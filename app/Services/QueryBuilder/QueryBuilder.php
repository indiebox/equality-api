<?php

namespace App\Services\QueryBuilder;

use App\Services\QueryBuilder\Traits\AddsFieldsToQuery;
use App\Services\QueryBuilder\Traits\AddsIncludesToQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LogicException;
use Spatie\QueryBuilder\QueryBuilder as BaseQueryBuilder;
use Spatie\QueryBuilder\QueryBuilderRequest;

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

    public $subjectIsCollection = false;

    protected $freshQuery = null;

    /**
     * Check if the field is requested.
     * @param string $field The field (ex. `users.id`)
     * @param boolean $isDefault Include this field by default if there are no any requested fields.
     * @return boolean Returns `true` if this field should be added to response.
     */
    public static function hasField($field, $isDefault = false)
    {
        $field = explode(".", $field);
        $fieldName = array_pop($field);

        $request = app(QueryBuilderRequest::class);
        $requestedFields = $request->fields()->get(implode(".", $field)) ?? [];

        if (empty($requestedFields) && $isDefault) {
            $requestedFields = [$fieldName];
        }

        return in_array($fieldName, $requestedFields, true);
    }

    /**
     * Check if the include is requested.
     * @param string $include The include (ex. `teams`)
     * @param boolean $isDefault This is the default include.
     * @param boolean $canBeNested If `true`, requested include like `teams.nested` will return true for `nested` check.
     * @return boolean Returns `true` if this include should be added to response.
     */
    public static function hasInclude($include, $isDefault = false, $inlcudeNested = true)
    {
        $request = app(QueryBuilderRequest::class);
        $requestedFields = $request->includes()->toArray();

        if (empty($requestedFields) && $isDefault) {
            $requestedFields = [$include];
        }

        return !is_null(Arr::first($requestedFields, function ($value) use ($include, $inlcudeNested) {
            return $inlcudeNested
                ? $value === $include || Str::contains($value, [".{$include}", "{$include}."])
                : $value === $include;
        }));
    }

    /**
     * Make new `QueryBuilder` instance for the specified object.
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
        return $this->traitAllowedFields($fields, $defaultFields, $defaultName);
    }

    #endregion

    #region AddsIncludesToQuery trait

    /**
     * Setup allowed includes.
     *
     * Default includes will be automatically added to allowed includes.
     *
     * @param array $includes Allowed includes.
     * @param array $defaultIncludes Default includes, if no any requested.
     * @param bool $unsetRelations Unset all relations that are not requested.
     * @return self
     */
    public function allowedIncludes($includes, $defaultIncludes = [], $unsetRelations = true): self
    {
        if ($this->subjectIsCollection) {
            throw new LogicException("Method 'allowedIncludes' can`t be used with collection.");
        }

        return $this->traitAllowedIncludes($includes, $defaultIncludes, $unsetRelations);
    }

    #endregion

    #region SortsQuery trait

    /**
     * Setup allowed sorts.
     * @param array $sorts Allowed sorts.
     * @return self
     */
    public function allowedSorts($sorts): self
    {
        if ($this->subjectIsModel || $this->subjectIsCollection) {
            throw new LogicException("Method 'allowedSorts' can`t be used with loaded model(s).");
        }

        return parent::allowedSorts(...func_get_args());
    }

    #endregion

    #region FiltersQuery trait

    /**
     * Setup allowed filters.
     * @param array $filters Allowed filters.
     * @return self
     */
    public function allowedFilters($filters): self
    {
        if ($this->subjectIsModel || $this->subjectIsCollection) {
            throw new LogicException("Method 'allowedFilters' can`t be used with loaded model(s).");
        }

        if ($this->request->filters()->isEmpty()) {
            // We haven't got any requested filters. No need to parse allowed filters.

            return $this;
        }

        return parent::allowedFilters(...func_get_args());
    }

    #endregion

    /**
     * Gets results.
     * @return mixed
     */
    public function get()
    {
        if ($this->subjectIsModel || $this->subjectIsCollection) {
            $result = $this->subject;
        } else {
            $result = $this->__call('get', func_get_args());
        }

        $this->applyFieldsToResult($result);

        $this->freshQuery = null;

        return $result;
    }

    public function getEloquentBuilder(): Builder
    {
        if ($this->subjectIsModel) {
            return $this->freshQuery ?? $this->freshQuery = $this->subject->newQueryWithoutRelationships();
        }

        return parent::getEloquentBuilder();
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

        if ($subject instanceof Collection) {
            $this->subjectIsCollection = true;
            $this->subject = $subject;

            return $this;
        }

        return parent::initializeSubject($subject);
    }
}
