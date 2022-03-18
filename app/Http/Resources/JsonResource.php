<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource as BaseResource;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Support\Arr;

class JsonResource extends BaseResource
{
    public static $allowedFields = [];

    public static $defaultFields = [];

    protected $resourceName = null;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource, $resourceName = null)
    {
        parent::__construct($resource);

        // if ($resourceName == null) {
            $resourceName = $this->getModel()->getTable();
        // }

        $this->resourceName = $resourceName;
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
        if (!array_key_exists($field, $this->resource->getAttributes())) {
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
            if (func_num_args() == 1) {
                return $this->resource->{$include};
            }

            return value($value);
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
