<?php

namespace App\Services\QueryBuilder\Traits;

use App\Services\QueryBuilder\Includes\IncludeRelationship;
use App\Services\QueryBuilder\Includes\LoadCount;
use App\Services\QueryBuilder\Includes\LoadRelationship;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\Concerns\AddsIncludesToQuery as ConcernsAddsIncludesToQuery;
use Spatie\QueryBuilder\Includes\IncludedCount;
use Spatie\QueryBuilder\Includes\IncludeInterface;

trait AddsIncludesToQuery
{
    use ConcernsAddsIncludesToQuery {
        addIncludesToQuery as private parentAddIncludesToQuery;
    }

    /**
     * Keys of relations to load.
     * @var array
     */
    public $loadRelations = [];

    /**
     * Keys of relations to load count.
     * @var array
     */
    public $loadCount = [];

    protected $defaultIncludes;

    public function allowedIncludes($includes, $defaultIncludes = []): self
    {
        $hasRequestedIncludes = !$this->request->includes()->isEmpty();

        $this->allowedIncludes = $this->parseIncludes(collect($includes)->push(...$defaultIncludes));
        $this->defaultIncludes = collect($defaultIncludes);

        $this->ensureAllIncludesExist();

        $includes = $hasRequestedIncludes
            ? $this->request->includes()
            : $this->defaultIncludes;

        $this->addIncludesToQuery($includes);

        return $this;
    }

    protected function parseIncludes($includes)
    {
        return $includes
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

                // Count includes
                if (Str::endsWith($include, config('query-builder.count_suffix'))) {
                    if ($this->subjectIsModel) {
                        return AllowedInclude::custom($include, new LoadCount($this), null);
                    }

                    return AllowedInclude::custom($include, new IncludedCount(), null);
                }

                // Relations includes
                $internalName = $internalName ?? $include;
                $relationshipClass = $this->subjectIsModel ? LoadRelationship::class : IncludeRelationship::class;
                $countClass = $this->subjectIsModel ? LoadCount::class : IncludedCount::class;

                return $relationshipClass::getIndividualRelationshipPathsFromInclude($internalName)
                    ->zip($relationshipClass::getIndividualRelationshipPathsFromInclude($include))
                    ->flatMap(function ($args) use ($relationshipClass, $countClass): Collection {
                        [$relationship, $alias] = $args;

                        $includes = AllowedInclude::custom($alias, new $relationshipClass($this), $relationship);

                        if (! Str::contains($relationship, '.')) {
                            $suffix = config('query-builder.count_suffix');

                            $includes = $includes->merge(
                                AllowedInclude::custom($alias . $suffix, new $countClass($this), $relationship . $suffix),
                            );
                        }

                        return $includes;
                    });
            })
            ->unique(function (AllowedInclude $allowedInclude) {
                return $allowedInclude->getName();
            });
    }

    protected function addIncludesToQuery(Collection $includes)
    {
        $this->parentAddIncludesToQuery($includes);

        if ($this->subjectIsModel) {
            $this->parseLoadRelations();
        }
    }

    protected function parseLoadRelations()
    {
        if (count($this->loadRelations) > 0) {
            $this->subject->loadMissing($this->loadRelations);
        }

        $loadCounts = $this->loadCount;

        // We are checking whether a relation has been loaded, for which we need to calculate a count.
        // If yes, we do not load the count with an additional query, but we just count the number of items
        // in the loaded collection.
        foreach ($loadCounts as $key => $relation) {
            $relationLoaded = in_array($relation, $this->loadRelations, true)
                || array_key_exists($relation, $this->loadRelations);

            if ($relationLoaded) {
                $this->subject->{$relation . config('query-builder.count_suffix')} = $this->subject->{$relation}->count();
                unset($loadCounts[$key]);
            }
        }

        if (count($loadCounts) > 0) {
            $this->subject->loadCount($loadCounts);
        }
    }
}
