<?php

namespace App\Services\QueryBuilder\Traits;

use App\Services\QueryBuilder\Exceptions\InvalidPaginationQuery;
use Illuminate\Http\Response;
use LogicException;

trait AddsPaginationToQuery
{
    protected $perPage = null;

    /**
     * Paginate the given query into a cursor paginator.
     *
     * @param  int|null  $defaultPerPage
     * @param  array  $columns
     * @param  string  $cursorName
     * @param  \Illuminate\Pagination\Cursor|string|null  $cursor
     * @return \Illuminate\Contracts\Pagination\CursorPaginator
     */
    public function cursorPaginate($defaultPerPage = null, $columns = ['*'], $cursorName = 'cursor', $cursor = null)
    {
        if ($this->subjectIsModel || $this->subjectIsCollection) {
            throw new LogicException("Method 'cursorPaginate' can`t be used with loaded model(s).");
        }

        $args = func_get_args();
        $args[0] = $this->perPage ?? $defaultPerPage;

        return $this->getResults(__FUNCTION__, $args)->withQueryString();
    }

    public function allowCursorPagination($max = null, $min = null)
    {
        if ($this->subjectIsModel || $this->subjectIsCollection) {
            throw new LogicException("Method 'allowCursorPagination' can`t be used with loaded model(s).");
        }

        $min ??= config('query-builder.pagination.min_count');
        $max ??= config('query-builder.pagination.max_count');

        $this->ensureCursorPaginationValid($min, $max);

        $this->perPage = $this->getPaginationData()['count'] ?? null;

        return $this;
    }

    protected function getPaginationData()
    {
        $key = config('query-builder.pagination.parameter');
        if (config('query-builder.request_data_source') === 'body') {
            return $this->request->input($key, []);
        }

        return $this->request->get($key, []);
    }

    protected function ensureCursorPaginationValid($min, $max)
    {
        $count = $this->getPaginationData()['count'] ?? null;
        if ($count == null) {
            return;
        }

        if ($count < $min || $count > $max) {
            throw new InvalidPaginationQuery(
                Response::HTTP_BAD_REQUEST,
                "Count of items per page must be between {$min} and {$max}."
            );
        }
    }
}
