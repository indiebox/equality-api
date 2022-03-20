<?php

namespace App\Services\QueryBuilder;

use App\Services\QueryBuilder\Includes\IncludeCount;
use App\Services\QueryBuilder\Includes\IncludeRelationship;
use App\Services\QueryBuilder\Includes\LoadCount;
use App\Services\QueryBuilder\Includes\LoadRelationship;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\Includes\IncludeInterface;
use Spatie\QueryBuilder\QueryBuilder as BaseQueryBuilder;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class QueryBuilder extends BaseQueryBuilder
{
    public $subjectIsModel = false;

    public $loadRelations = [];

    public $loadCount = [];

    public $withRelations = [];

    public $withCount = [];

    public $freshQuery = null;

    public function get()
    {
        if ($this->subjectIsModel) {
            return $this->subject;
        }

        $result = $this->__call('get', func_get_args());

        if (count($this->withCount) > 0) {
            $result->map(function ($model) {
                foreach ($this->withCount as $key => $relation) {
                    if ($model->relationLoaded($relation)) {
                        $model->{$relation . config('query-builder.count_suffix')} = $model->{$relation}->count();
                        unset($this->withCount[$key]);
                    } else {
                        // TODO: throw error?
                    }
                }
            });
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

    #region AddsIncludesToQuery trait

    public function allowedIncludes($includes): self
    {
        $includes = is_array($includes) ? $includes : func_get_args();

        $this->allowedIncludes = collect($includes)
            ->reject(function ($include) {
                return empty($include);
            })
            ->flatMap(function ($include): Collection {
                if ($include instanceof Collection) {
                    return $include;
                }

                if ($include instanceof IncludeInterface) {
                    return collect([$include]);
                }

                if (Str::endsWith($include, config('query-builder.count_suffix'))) {
                    if ($this->subjectIsModel) {
                        return AllowedInclude::custom($include, new LoadCount($this), null);
                    }

                    return AllowedInclude::custom($include, new IncludeCount($this), null);
                }

                $internalName = $internalName ?? $include;
                if ($this->subjectIsModel) {
                    return LoadRelationship::getIndividualRelationshipPathsFromInclude($internalName)
                        ->zip(LoadRelationship::getIndividualRelationshipPathsFromInclude($include))
                        ->flatMap(function ($args): Collection {
                            [$relationship, $alias] = $args;

                            $includes = AllowedInclude::custom($alias, new LoadRelationship($this), $relationship);

                            if (! Str::contains($relationship, '.')) {
                                $suffix = config('query-builder.count_suffix');

                                $includes = $includes->merge(
                                    AllowedInclude::custom($alias . $suffix, new LoadCount($this), $relationship . $suffix),
                                );
                            }

                            return $includes;
                        });
                } else {
                    return LoadRelationship::getIndividualRelationshipPathsFromInclude($internalName)
                        ->zip(LoadRelationship::getIndividualRelationshipPathsFromInclude($include))
                        ->flatMap(function ($args): Collection {
                            [$relationship, $alias] = $args;

                            $includes = AllowedInclude::custom($alias, new IncludeRelationship($this), $relationship);

                            if (! Str::contains($relationship, '.')) {
                                $suffix = config('query-builder.count_suffix');

                                $includes = $includes->merge(
                                    AllowedInclude::custom($alias . $suffix, new IncludeCount($this), $relationship . $suffix),
                                );
                            }

                            return $includes;
                        });
                }
            })
            ->unique(function (AllowedInclude $allowedInclude) {
                return $allowedInclude->getName();
            });

        $this->ensureAllIncludesExist();

        $this->addIncludesToQuery($this->request->includes());

        return $this;
    }

    protected function addIncludesToQuery(Collection $includes)
    {
        parent::addIncludesToQuery($includes);

        if ($this->subjectIsModel) {
            $this->parseLoadRelations();
        } else {
            $this->parseIncludeRelations();
        }
    }

    protected function parseIncludeRelations()
    {
        $query = $this->getEloquentBuilder();

        if (count($this->withRelations) > 0) {
            $query->with($this->withRelations);
        }

        $withCount = $this->withCount;
        $addWith = [];
        foreach ($withCount as $key => $relation) {
            $relationLoaded = !is_null(
                Arr::first($this->withRelations, fn($value, $key) => $value === $relation || $key === $relation)
            );

            if (!$relationLoaded) {
                $addWith[$key] = $relation;
                unset($withCount[$key]);
            }
        }

        if (count($addWith) > 0) {
            $query->withCount($addWith);
        }
    }

    protected function parseLoadRelations()
    {
        if (count($this->loadRelations) > 0) {
            $this->subject->load($this->loadRelations);
        }

        $loadCounts = $this->loadCount;
        foreach ($loadCounts as $key => $relation) {
            $relationLoaded = !is_null(
                Arr::first($this->loadRelations, fn($value, $key) => $value === $relation || $key === $relation)
            );

            if ($relationLoaded) {
                $this->subject->{$relation . config('query-builder.count_suffix')} = $this->subject->{$relation}->count();
                unset($loadCounts[$key]);
            }
        }

        if (count($loadCounts) > 0) {
            $this->subject->loadCount($loadCounts);
        }
    }

    #endregion
}
