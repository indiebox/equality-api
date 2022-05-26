<?php

/**
 * @see https://github.com/spatie/laravel-query-builder
 */

return [

    /*
     * By default the package will use the `include`, `filter`, `sort`
     * and `fields` query parameters as described in the readme.
     *
     * You can customize these query string parameters here.
     */
    'parameters' => [
        'include' => 'include',

        'filter' => 'filter',

        'sort' => 'sort',

        'fields' => 'fields',

        'append' => 'append',
    ],

    /*
     * Pagination settings.
     */
    'pagination' => [
        // The name of the query parameter.
        'parameter' => 'page',

        // The default min items count.
        'min_count' => 10,

        // The default max items count.
        'max_count' => 100,

        // The page[x] key for number of the page(dont work with cursor pagination).
        'number' => 'number',

        // The page[x] key for count of the items per page.
        'count' => 'count',
    ],

    /*
     * Related model counts are included using the relationship name suffixed with this string.
     * For example: GET /users?include=posts_count
     */
    'count_suffix' => '_count',

    /*
     * By default the package will throw an `InvalidFilterQuery` exception when a filter in the
     * URL is not allowed in the `allowedFilters()` method.
     */
    'disable_invalid_filter_query_exception' => false,

    /*
     * By default the package inspects query string of request using $request->query().
     * You can change this behavior to inspect the request body using $request->input()
     * by setting this value to `body`.
     *
     * Possible values: `query_string`, `body`
     */
    'request_data_source' => 'query_string',
];
