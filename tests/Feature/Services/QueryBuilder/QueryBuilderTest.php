<?php

namespace Tests\Feature\Services\QueryBuilder;

use App\Services\QueryBuilder\Exceptions\SortQueryException;
use App\Services\QueryBuilder\QueryBuilder;
use App\Services\QueryBuilder\Sorts\SortRelationsCount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use LogicException;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilderRequest;
use Tests\Feature\Services\QueryBuilder\Stubs\QueryableModel;
use Tests\TestCase;

class QueryBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
    }

    protected function tearDown(): void
    {
        $this->schema()->drop('models');
        $this->schema()->drop('related_models');
    }

    public function createSchema()
    {
        $this->schema()->create('models', function ($table) {
            $table->increments('id');
            $table->string('name')->default('default name');
            $table->string('description')->nullable();
            $table->timestamp('timestamp')->nullable();
            $table->timestamps();
        });

        $this->schema()->create('related_models', function ($table) {
            $table->increments('id');
            $table->integer('model_id')->nullable();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });
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

    public function test_cant_use_sorts_at_existing_model()
    {
        $model = $this->createModel();

        $this->expectException(LogicException::class);

        $result = QueryBuilder::for($model)->allowedSorts([])->get();
    }
    public function test_cant_use_filters_at_existing_model()
    {
        $model = $this->createModel();

        $this->expectException(LogicException::class);

        $result = QueryBuilder::for($model)->allowedFilters([])->get();
    }

    public function test_can_set_allowed_fields()
    {
        $this->createModel();

        $result = QueryBuilder::for(QueryableModel::query(), $this->withFields(['models' => 'id,name']))
            ->allowedFields(['id', 'name']);

        $this->assertEquals("select * from `models`", $result->toSql());

        $result = $result->get();

        $this->assertEquals([
            'description',
            'timestamp',
            'created_at',
            'updated_at',
        ], $result->first()->getHidden());
    }
    public function test_can_set_default_fields()
    {
        $this->createModel();

        $result = QueryBuilder::for(QueryableModel::query())
            ->allowedFields(['id', 'name'], ['id']);

        $this->assertEquals("select * from `models`", $result->toSql());

        $result = $result->get();

        $this->assertEquals([
            'name',
            'description',
            'timestamp',
            'created_at',
            'updated_at',
        ], $result->first()->getHidden());
    }
    public function test_requested_fields_overrides_default()
    {
        $this->createModel();

        $result = QueryBuilder::for(QueryableModel::query(), $this->withFields(['models' => 'name']))
            ->allowedFields(['id', 'name'], ['id']);

        $this->assertEquals("select * from `models`", $result->toSql());

        $result = $result->get();

        $this->assertEquals([
            'id',
            'description',
            'timestamp',
            'created_at',
            'updated_at',
        ], $result->first()->getHidden());
    }
    public function test_can_set_alias_for_current_fields()
    {
        $this->createModel();

        $result = QueryBuilder::for(QueryableModel::query(), $this->withFields(['alias' => 'id,name']))
            ->allowedFields(['id', 'name'], ['id'], 'alias');

        $this->assertEquals("select * from `models`", $result->toSql());

        $result = $result->get();

        $this->assertEquals([
            'description',
            'timestamp',
            'created_at',
            'updated_at',
        ], $result->first()->getHidden());
    }

    /*
     * SortRelationCount custom sort.
     */
    public function test_sort_relations_count_not_available_without_include_count()
    {
        $this->expectException(SortQueryException::class);

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

        $result = QueryBuilder::for(QueryableModel::query(), new Request(['include' => 'related_count', 'sort' => 'related_count']))
            ->allowedSorts([AllowedSort::custom('related_count', new SortRelationsCount('related'))])
            ->allowedIncludes(['related_count'])
            ->toSql();

        $this->assertSame(
            "select `models`.*, (select count(*) from `related_models` where `models`.`id` = `related_models`.`model_id`) as `related_count` from `models` order by `related_count` asc",
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

    protected function schema()
    {
        return $this->connection()->getSchemaBuilder();
    }

    protected function connection()
    {
        return Model::getConnectionResolver()->connection();
    }
}
