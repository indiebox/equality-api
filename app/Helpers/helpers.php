<?php

use Illuminate\Http\Resources\MissingValue;
use Illuminate\Support\Facades\URL;

if (!function_exists('image')) {
    /** Generate full url for image from storage.
     * @param string|null $path Image path.
     * If `$path` is a valid url it will be returned without changes.
     *
     * @return string Return full url to the specified image.
     *
     * @example /
     * ```
     * image('images/test.jpg')  // asset('images/test.jpg')
     * image('http://some.site') // http://some.site
     * ```
     */
    function image($path)
    {
        if ($path == null) {
            return null;
        }

        if (URL::isValidUrl($path)) {
            return $path;
        }

        return asset('storage/' . $path);
    }
}

if (!function_exists('field')) {
    /** Get field if its not null or return MissingValue otherwise.
     * @param mixed $value Value.
     * If `$value` is not a null it will be returned without changes.
     * @param Closure $callback Callback that will be called when `$value` is not a null.
     *
     * @return mixed Returns `$value` or `MissingValue` on null.
     *
     * @example /
     * ```
     * // TeamResource.php
     *
     * return [
     *     'url' => field(image($this->url)),
     *     'description' => field($this->description),
     * ];
     * ```
     */
    function field($value, Closure $callback = null)
    {
        if ($value === null) {
            return new MissingValue();
        }

        if ($callback != null) {
            return $callback($value);
        }

        return $value;
    }
}

// Function-wrappers for debugbar in production mode.
if (!function_exists('debugbar')) {
    function debugbar()
    {
        return optional();
    }
}
if (!function_exists('debug')) {
    function debug($value)
    {
        return optional();
    }
}
