<?php

namespace Tests\Feature\Services\QueryBuilder;

use App\Services\QueryBuilder\Exceptions\InvalidPaginationQuery;
use App\Services\QueryBuilder\QueryBuilder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use LogicException;
use Tests\Feature\Services\QueryBuilder\Stubs\QueryableModel;
use Tests\TestCase;

class AddsPaginationToQueryTest extends TestCase
{
    use DatabaseTransactions;

    protected function setupDatabase($schema)
    {
        parent::setupDatabase($schema);

        if ($schema->hasTable('models')) {
            return;
        }

        $schema->create('models', function ($table) {
            $table->increments('id');
            $table->string('name')->default('default name');
            $table->string('description')->nullable();
            $table->timestamp('timestamp')->nullable();
            $table->timestamps();
        });
    }

    protected static function clearTestsData($schema)
    {
        $schema->dropIfExists('models');
    }

    /*
     * Tests
     */
    public function test_cant_use_pagination_at_existing_model()
    {
        $model = $this->createModel();

        $this->expectException(LogicException::class);

        QueryBuilder::for($model)->allowCursorPagination()->cursorPaginate();
    }
    public function test_cant_use_pagination_at_collection()
    {
        $model = collect($this->createModel());

        $this->expectException(LogicException::class);

        QueryBuilder::for($model)->allowCursorPagination()->cursorPaginate();
    }

    public function test_cant_use_cursor_pagination_count_lower_than_min()
    {
        $this->expectException(InvalidPaginationQuery::class);

        QueryBuilder::for(QueryableModel::query(), $this->withPaginationCount(1))
            ->allowCursorPagination(30, 10)
            ->cursorPaginate(10);
    }
    public function test_cant_use_cursor_pagination_count_greater_than_max()
    {
        $this->expectException(InvalidPaginationQuery::class);

        QueryBuilder::for(QueryableModel::query(), $this->withPaginationCount(31))
            ->allowCursorPagination(30, 10)
            ->cursorPaginate(10);
    }

    public function test_allow_cursor_pagination_applies_default_count()
    {
        $result = QueryBuilder::for(QueryableModel::query())
            ->allowCursorPagination()
            ->cursorPaginate(15);

        $this->assertEquals(15, $result->perPage());
    }
    public function test_allow_cursor_pagination_applied()
    {
        $this->createModel();

        $result = QueryBuilder::for(QueryableModel::query(), $this->withPaginationCount(15))
            ->allowCursorPagination(30, 10)
            ->cursorPaginate(10);

        $this->assertEquals(15, $result->perPage());
    }
    public function test_append_query_string_to_cursor_pagination_result()
    {
        $this->createModel();
        $this->createModel();

        Paginator::queryStringResolver(fn() => ['test' => 1, 'test2' => 'test']);

        $result = QueryBuilder::for(QueryableModel::query())
            ->allowCursorPagination(30, 1)
            ->cursorPaginate(1);

        $this->assertEquals(1, $result->perPage());
        $this->assertStringContainsString('test=1&test2=test&cursor=' . $result->nextCursor()->encode(), $result->nextPageUrl());
    }

    /*
     * Helpers
     */
    public function withPaginationCount(int $count)
    {
        return new Request([
            'page' => ['count' => $count],
        ]);
    }

    public function createModel()
    {
        return QueryableModel::create()->fresh();
    }
}
