<?php

namespace App\Services\QueryBuilder\Contracts;

/**
 * This interface is used to allow include resource classes itself
 * to 'allowedFields' method of 'QueryBuilder' class.
 */
interface ResourceWithFields
{
    /**
     * Gets the default name of this resource.
     * @return string
     */
    public static function defaultName(): string;

    /**
     * Gets the default fields of this resource.
     * @return array
     */
    public static function defaultFields(): array;

    /**
     * Gets the allowed fields of this resoruce.
     * @return array
     */
    public static function allowedFields(): array;
}
