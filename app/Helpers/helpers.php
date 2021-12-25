<?php

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
