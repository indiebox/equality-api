<?php

namespace Tests\Feature\Services\QueryBuilder;

use App\Http\Kernel;
use App\Services\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\ParallelTesting;
use Spatie\QueryBuilder\Exceptions\InvalidFieldQuery;
use Tests\Feature\Services\QueryBuilder\Stubs\NestedModel;
use Tests\Feature\Services\QueryBuilder\Stubs\QueryableModel;
use Tests\Feature\Services\QueryBuilder\Stubs\RelatedModel;
use Tests\TestCase;

class AddsFieldsToQueryTest extends TestCase
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
            $table->integer('nested_id')->nullable();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        $schema->create('nested_models', function ($table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public static function tearDownAfterClass(): void
    {
        $app = require __DIR__ . '/../../../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        if (ParallelTesting::token()) {
            config(['database.connections.mysql.database' => 'equality_test_test_' . ParallelTesting::token()]);
        }

        $b = Model::getConnectionResolver()->connection()->getSchemaBuilder();

        $b->dropIfExists('models');
        $b->dropIfExists('related_models');
        $b->dropIfExists('nested_models');
    }

    /*
     * Tests
     */
    public function test_allowed_fields_not_applied_without_request()
    {
        $this->createModel();

        $result = QueryBuilder::for(QueryableModel::query())
            ->allowedFields(['id', 'name']);

        $result = $result->get();

        $this->assertEquals([
        ], $result->first()->getHidden());
    }
    public function test_allowed_fields_applied()
    {
        $this->createModel();

        $result = QueryBuilder::for(QueryableModel::query(), $this->withFields(['models' => 'id,name']))
            ->allowedFields(['id', 'name']);

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

        $result = $result->get();

        $this->assertEquals([
            'name',
            'description',
            'timestamp',
            'created_at',
            'updated_at',
        ], $result->first()->getHidden());
    }
    public function test_requested_fields_overrides_defaults()
    {
        $this->createModel();

        $result = QueryBuilder::for(QueryableModel::query(), $this->withFields(['models' => 'name']))
            ->allowedFields(['id', 'name'], ['id']);

        $result = $result->get();

        $this->assertEquals([
            'id',
            'description',
            'timestamp',
            'created_at',
            'updated_at',
        ], $result->first()->getHidden());
    }
    public function test_includes_fields_exists_in_result()
    {
        $this->createModel();

        $result = QueryBuilder::for(QueryableModel::withCount('related'), $this->withFields(['models' => 'name']))
            ->allowedFields(['id', 'name'], ['id'])
            ->allowedIncludes(['related_count']);

        $result = $result->get();

        $this->assertEquals([
            'id',
            'description',
            'timestamp',
            'created_at',
            'updated_at',
        ], $result->first()->getHidden());

        $result = QueryBuilder::for(QueryableModel::withCount('related'), $this->withFields(['models' => 'name']))
            ->allowedFields(['id', 'name'], ['id']);

        $result = $result->get();

        $this->assertEquals([
            'id',
            'description',
            'timestamp',
            'created_at',
            'updated_at',
            'related_count',
        ], $result->first()->getHidden());
    }

    public function test_can_set_alias_for_parent_model()
    {
        $this->createModel();

        $result = QueryBuilder::for(QueryableModel::query(), $this->withFields(['alias' => 'id,name']))
            ->allowedFields(['id', 'name'], ['id'], 'alias');

        $result = $result->get();

        $this->assertEquals([
            'description',
            'timestamp',
            'created_at',
            'updated_at',
        ], $result->first()->getHidden());

        $this->expectException(InvalidFieldQuery::class);

        $result = QueryBuilder::for(QueryableModel::query(), $this->withFields(['models' => 'id,name']))
            ->allowedFields(['id', 'name'], ['id'], 'alias');
    }

    public function test_related_allowed_fields_not_applied_without_request()
    {
        $this->createModelWithRelation();

        $result = QueryBuilder::for(QueryableModel::with('related'))
            ->allowedFields(['related.id', 'related.name']);

        $result = $result->get();

        $this->assertEquals([
        ], $result->first()->related->first()->getHidden());
    }
    public function test_related_allowed_fields_applied()
    {
        $this->createModelWithRelation();

        $result = QueryBuilder::for(QueryableModel::with('related'), $this->withFields(['related' => 'id,name']))
            ->allowedFields(['related.id', 'related.name']);

        $result = $result->get();

        $this->assertEquals([
            'model_id',
            'nested_id',
            'description',
            'created_at',
            'updated_at',
        ], $result->first()->related->first()->getHidden());
    }
    public function test_related_can_set_default_fields()
    {
        $this->createModelWithRelation();

        $result = QueryBuilder::for(QueryableModel::with('related'))
            ->allowedFields(['related.id', 'related.name'], ['related.id']);

        $result = $result->get();

        $this->assertEquals([
            'model_id',
            'nested_id',
            'name',
            'description',
            'created_at',
            'updated_at',
        ], $result->first()->related->first()->getHidden());
    }
    public function test_related_requested_fields_overrides_defaults()
    {
        $this->createModelWithRelation();

        $result = QueryBuilder::for(QueryableModel::with('related'), $this->withFields(['related' => 'name']))
            ->allowedFields(['related.id', 'related.name'], ['related.id']);

        $result = $result->get();

        $this->assertEquals([
            'id',
            'model_id',
            'nested_id',
            'description',
            'created_at',
            'updated_at',
        ], $result->first()->related->first()->getHidden());
    }

    public function test_nested_allowed_fields_not_applied_without_request()
    {
        $this->createModelWithNested();

        $result = QueryBuilder::for(QueryableModel::with('related.nested'))
            ->allowedFields(['related.nested.id', 'related.nested.name']);

        $result = $result->get();

        $this->assertEquals([
        ], $result->first()->related->first()->nested->getHidden());
    }
    public function test_nested_allowed_fields_applied()
    {
        $this->createModelWithNested();

        $result = QueryBuilder::for(QueryableModel::with('related.nested'), $this->withFields(['related.nested' => 'id,name']))
            ->allowedFields(['related.nested.id', 'related.nested.name']);

        $result = $result->get();

        $this->assertEquals([
            'description',
            'created_at',
            'updated_at',
        ], $result->first()->related->first()->nested->getHidden());
    }
    public function test_nested_can_set_default_fields()
    {
        $this->createModelWithNested();

        $result = QueryBuilder::for(QueryableModel::with('related.nested'))
            ->allowedFields(['related.nested.id', 'related.nested.name'], ['related.nested.id']);

        $result = $result->get();

        $this->assertEquals([
            'name',
            'description',
            'created_at',
            'updated_at',
        ], $result->first()->related->first()->nested->getHidden());
    }
    public function test_nested_requested_fields_overrides_defaults()
    {
        $this->createModelWithNested();

        $result = QueryBuilder::for(QueryableModel::with('related.nested'), $this->withFields(['related.nested' => 'name']))
            ->allowedFields(['related.nested.id', 'related.nested.name'], ['related.nested.id']);

        $result = $result->get();

        $this->assertEquals([
            'id',
            'description',
            'created_at',
            'updated_at',
        ], $result->first()->related->first()->nested->getHidden());
    }

    /*
     * Helpers
     */
    public function withFields($fields)
    {
        return new Request([
            'fields' => $fields,
        ]);
    }

    public function createModel()
    {
        QueryableModel::create()->fresh();
    }

    public function createModelWithRelation()
    {
        $model = QueryableModel::create();
        $model->related()->save(new RelatedModel());
    }

    public function createModelWithNested()
    {
        $model = QueryableModel::create();
        $related = new RelatedModel();
        $related->nested()->associate(NestedModel::create());

        $model->related()->save($related);
    }
}
