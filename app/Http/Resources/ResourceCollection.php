<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection as BaseResourceCollection;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;

class ResourceCollection extends BaseResourceCollection
{
    protected $resourceName = null;

    protected $loadRelationsOnRequest = false;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource, $resourceName = null)
    {
        $this->resourceName = $resourceName;

        parent::__construct($resource);
    }

    /**
     * Map the given collection resource into its individual resources.
     *
     * @param  mixed  $resource
     * @return mixed
     */
    protected function collectResource($resource)
    {
        if ($resource instanceof MissingValue) {
            return $resource;
        }

        if (is_array($resource)) {
            $resource = new Collection($resource);
        }

        $collects = $this->collects();

        if ($collects && ! $resource->first() instanceof $collects) {
            $collection = collect();
            foreach ($resource as $oneResource) {
                $class = new $collects($oneResource, $this->resourceName);
                $class->loadRelationsOnRequest = false;

                $collection->add($class);
            }

            $this->collection = $collection;
        } else {
            $this->collection = $resource->toBase();
        }

        return ($resource instanceof AbstractPaginator || $resource instanceof AbstractCursorPaginator)
                    ? $resource->setCollection($this->collection)
                    : $this->collection;
    }
}
