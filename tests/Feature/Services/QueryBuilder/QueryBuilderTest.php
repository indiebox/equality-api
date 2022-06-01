<?php

namespace Tests\Feature\Services\QueryBuilder;

use App\Services\QueryBuilder\Exceptions\InvalidSortQuery;
use App\Services\QueryBuilder\QueryBuilder;
use App\Services\QueryBuilder\Sorts\SortRelationsCount;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use LogicException;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilderRequest;
use Tests\Feature\Services\QueryBuilder\Stubs\QueryableModel;
use Tests\TestCase;

class QueryBuilderTest extends TestCase
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

        $schema->create('related_models', function ($table) {
            $table->increments('id');
            $table->integer('model_id')->nullable();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    protected static function clearTestsData($schema)
    {
        $schema->dropIfExists('models');
        $schema->dropIfExists('related_models');
    }

    /*
     * Tests
     */
    public function test_has_field_method()
    {
        app()->instance(
            QueryBuilderRequest::class,
            new QueryBuilderRequest(['fields' => ['models' => 'id,name', 'models.deep' => 'id']])
        );

        $this->assertTrue(QueryBuilder::hasField('models.id'));
        $this->assertTrue(QueryBuilder::hasField('models.name'));
        $this->assertTrue(QueryBuilder::hasField('models.deep.id'));
        $this->assertFalse(QueryBuilder::hasField('models.default', true));
        $this->assertFalse(QueryBuilder::hasField('models.field'));
        $this->assertFalse(QueryBuilder::hasField('models1.id'));
        $this->assertFalse(QueryBuilder::hasField('models1.deep.id'));

        app()->instance(QueryBuilderRequest::class, new QueryBuilderRequest());

        $this->assertFalse(QueryBuilder::hasField('models.id'));
        $this->assertFalse(QueryBuilder::hasField('models.name'));
        $this->assertFalse(QueryBuilder::hasField('models.deep.id'));
        $this->assertTrue(QueryBuilder::hasField('models.default', true));
        $this->assertFalse(QueryBuilder::hasField('models.field'));
        $this->assertTrue(QueryBuilder::hasField('models1.id', true));
        $this->assertTrue(QueryBuilder::hasField('models1.deep.id', true));
    }
    public function test_has_include_method()
    {
        app()->instance(
            QueryBuilderRequest::class,
            new QueryBuilderRequest(['include' => ['relation', 'models.deep']])
        );

        $this->assertTrue(QueryBuilder::hasInclude('relation'));
        $this->assertTrue(QueryBuilder::hasInclude('relation', false, false));
        $this->assertTrue(QueryBuilder::hasInclude('models.deep'));
        $this->assertTrue(QueryBuilder::hasInclude('models.deep', false, false));
        $this->assertTrue(QueryBuilder::hasInclude('models'));
        $this->assertTrue(QueryBuilder::hasInclude('deep'));
        $this->assertFalse(QueryBuilder::hasInclude('models', false, false));
        $this->assertFalse(QueryBuilder::hasInclude('deep', false, false));

        $this->assertFalse(QueryBuilder::hasInclude('nested', true, true));
        $this->assertFalse(QueryBuilder::hasInclude('nested.deep'));

        app()->instance(QueryBuilderRequest::class, new QueryBuilderRequest());

        $this->assertFalse(QueryBuilder::hasInclude('relation'));
        $this->assertFalse(QueryBuilder::hasInclude('relation', false, false));
        $this->assertFalse(QueryBuilder::hasInclude('models.deep'));
        $this->assertFalse(QueryBuilder::hasInclude('models.deep', false, false));
        $this->assertFalse(QueryBuilder::hasInclude('models'));
        $this->assertFalse(QueryBuilder::hasInclude('deep'));
        $this->assertFalse(QueryBuilder::hasInclude('models', false, false));
        $this->assertFalse(QueryBuilder::hasInclude('deep', false, false));

        $this->assertTrue(QueryBuilder::hasInclude('nested', true, true));
        $this->assertFalse(QueryBuilder::hasInclude('nested.deep'));
    }

    public function test_can_get_models_from_query()
    {
        $model = $this->createModel();

        $result = QueryBuilder::for(QueryableModel::query())->get();

        $this->assertCount(1, $result);
        $this->assertEquals($model, $result->first());
    }

    public function test_can_get_model_itself()
    {
        $model = $this->createModel();

        $result = QueryBuilder::for($model)->get();

        $this->assertSame($model, $result);
    }

    public function test_can_get_collection_itself()
    {
        $model = collect($this->createModel());

        $result = QueryBuilder::for($model)->get();

        $this->assertSame($model, $result);
    }

    public function test_cant_use_sorts_at_existing_model()
    {
        $model = $this->createModel();

        $this->expectException(LogicException::class);

        $result = QueryBuilder::for($model)->allowedSorts([])->get();
    }
    public function test_cant_use_sorts_at_collection()
    {
        $model = collect($this->createModel());

        $this->expectException(LogicException::class);

        $result = QueryBuilder::for($model)->allowedSorts([])->get();
    }
    public function test_cant_use_filters_at_existing_model()
    {
        $model = $this->createModel();

        $this->expectException(LogicException::class);

        $result = QueryBuilder::for($model)->allowedFilters([])->get();
    }
    public function test_cant_use_filters_at_collection()
    {
        $model = collect($this->createModel());

        $this->expectException(LogicException::class);

        $result = QueryBuilder::for($model)->allowedFilters([])->get();
    }
    public function test_cant_use_includes_at_collection()
    {
        $model = collect($this->createModel());

        $this->expectException(LogicException::class);

        $result = QueryBuilder::for($model)->allowedIncludes([])->get();
    }

    /*
     * SortRelationCount custom sort.
     */
    public function test_sort_relations_count_not_available_without_include_count()
    {
        $this->expectException(InvalidSortQuery::class);

        $result = QueryBuilder::for(QueryableModel::query(), $this->withSorts('related_count'))
            ->allowedSorts([AllowedSort::custom('related_count', new SortRelationsCount('related'))])
            ->allowedIncludes(['related_count'])
            ->get();
    }
    public function test_sort_relations_count_available_with_included_count()
    {
        app()->instance(
            QueryBuilderRequest::class,
            new QueryBuilderRequest(['include' => 'related_count', 'sort' => 'related_count'])
        );

        $result = QueryBuilder::for(
            QueryableModel::query(),
            new Request(['include' => 'related_count', 'sort' => 'related_count'])
        )
        ->allowedSorts([AllowedSort::custom('related_count', new SortRelationsCount('related'))])
        ->allowedIncludes(['related_count'])
        ->toSql();

        $this->assertSame(
            "select `models`.*, (select count(*) from `related_models` where `models`.`id` = `related_models`.`model_id`)"
            . " as `related_count` from `models` order by `related_count` asc",
            $result
        );
    }

    /*
     * Helpers
     */
    public function withFields(array $fields)
    {
        return new Request([
            'fields' => $fields,
        ]);
    }

    public function withIncludes(string $includes)
    {
        return new Request([
            'include' => $includes,
        ]);
    }

    public function withSorts(string $sorts)
    {
        return new Request([
            'sort' => $sorts,
        ]);
    }

    public function createModel()
    {
        return QueryableModel::create()->fresh();
    }
}
