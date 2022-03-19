<?php

namespace App\Http\Resources;

class AnonymousResourceCollection extends ResourceCollection
{
    /**
     * The name of the resource being collected.
     *
     * @var string
     */
    public $collects;

    /**
     * Create a new anonymous resource collection.
     *
     * @param  mixed  $resource
     * @param  string  $collects
     * @return void
     */
    public function __construct($resource, $collects, $resourceName = null)
    {
        $this->collects = $collects;

        parent::__construct($resource, $resourceName);
    }
}
