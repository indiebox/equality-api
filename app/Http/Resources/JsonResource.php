<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource as BaseResource;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Support\Arr;

class JsonResource extends BaseResource
{
    public static $allowedFields = [];

    public static function getAllowedFields($resourceName = null)
    {
        $result = static::$allowedFields;

        if ($resourceName != null) {
            foreach ($result as $key => $field) {
                $result[$key] = $resourceName . "." . $field;
            }
        }

        return $result;
    }

    public static $defaultFields = [];

    protected $resourceName = null;

    public $loadRelationsOnRequest = true;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource, $resourceName = null)
    {
        parent::__construct($resource);

        if ($this->resourceName !== null) {
            return;
        }

        if (
            $resourceName == null
            && !($this->resource instanceof MissingValue)
        ) {
            $resourceName = $this->getModel()->getTable();
        }

        $this->resourceName = $resourceName;
    }

    /**
     * Create a new anonymous resource collection.
     *
     * @param  mixed  $resource
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public static function collection($resource, $resourceName = null)
    {
        return tap(new AnonymousResourceCollection($resource, static::class, $resourceName), function ($collection) {
            if (property_exists(static::class, 'preserveKeys')) {
                $collection->preserveKeys = (new static([]))->preserveKeys === true;
            }
        });
    }

    /**
     * Include a column in response if it has been requested.
     *
     * @param  string  $field
     * @param  mixed  $value
     * @return \Illuminate\Http\Resources\MissingValue|mixed
     */
    public function whenFieldRequested($field, $value = null)
    {
        [$fieldPath, $field] = $this->prependSourceName($field);

        // If our model doesnt have loaded attribute we just return MissingValue.
        if (
            func_get_args() == 1
            && !array_key_exists($field, $this->resource->getAttributes())
        ) {
            return new MissingValue();
        }

        $queryField = Arr::get(request()->query(), 'fields.' . $fieldPath);
        if ($queryField === null || !is_string($queryField)) {
            if (Arr::first(static::$defaultFields, fn($value) => $value == $field) != null) {
                if (func_num_args() == 1) {
                    return $this->resource->{$field};
                }

                return value($value);
            }
        }

        $requestValues = explode(",", $queryField);
        if ($this->isRequested($requestValues, $field)) {
            if (func_num_args() == 1) {
                return $this->resource->{$field};
            }

            return value($value);
        }

        return new MissingValue();
    }

    public function whenFilled($field, $value = null)
    {
        if (isset($this->resource->{$field})) {
            if (func_num_args() == 1) {
                return $this->resource->{$field};
            }

            return value($value);
        }

        return new MissingValue();
    }

    /**
     * Retrieve and include a relationship in response if it has been requested.
     *
     * @param  string  $include
     * @param  mixed  $value
     * @return \Illuminate\Http\Resources\MissingValue|mixed
     */
    public function whenIncludeRequested($include, $value = null)
    {
        if ($this->isIncludeRequested($include)) {
            // Relation count
            if (str_ends_with($include, config('query-builder.count_suffix'))) {
                if (!array_key_exists($include, $this->resource->getAttributes())) {
                    if ($this->loadRelationsOnRequest) {
                        $this->resource->loadCount(explode(config('query-builder.count_suffix'), $include)[0]);

                        return $this->resource->{$include};
                    }

                    return new MissingValue();
                } else {
                    return $this->resource->{$include};
                }
            } else {
                if (func_num_args() == 1) {
                    if ($this->loadRelationsOnRequest) {
                        return $this->resource->{$include};
                    } else {
                        return $this->whenLoaded(...func_get_args());
                    }
                } else {
                    return value($value);
                }
            }
        }

        return new MissingValue();
    }

    protected function prependSourceName($field)
    {
        $exploded = explode(".", $field);
        if (count($exploded) == 1) {
            $exploded = Arr::prepend($exploded, $this->resourceName);
        }

        $fieldName = Arr::pull($exploded, count($exploded) - 1);
        return [implode(".", $exploded), $fieldName];
    }

    protected function isIncludeRequested($include)
    {
        $queryField = request()->query('include');
        if ($queryField === null || !is_string($queryField)) {
            return false;
        }

        $requestValues = explode(",", $queryField);

        return $this->isRequested($requestValues, $include);
    }

    protected function isRequested($requestedValues, $value)
    {
        return !is_null(Arr::first($requestedValues, fn($val) => $val == $value));
    }
}
