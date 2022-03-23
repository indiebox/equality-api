<?php

namespace Tests\Feature\Services\QueryBuilder;

use App\Services\QueryBuilder\QueryBuilder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\Feature\Services\QueryBuilder\Stubs\NestedModel;
use Tests\Feature\Services\QueryBuilder\Stubs\QueryableModel;
use Tests\Feature\Services\QueryBuilder\Stubs\RelatedModel;
use Tests\TestCase;

class AddsIncludesToQueryTest extends TestCase
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
            $table->integer('model2_id')->nullable();
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

    protected static function clearTestsData($schema)
    {
        $schema->dropIfExists('models');
        $schema->dropIfExists('related_models');
        $schema->dropIfExists('nested_models');
    }

    /*
     * Tests
     */
    public function test_allowed_includes_not_applied_without_request()
    {
        $model = $this->createModel();

        $result = QueryBuilder::for(QueryableModel::query())
            ->allowedIncludes(['related'])
            ->get();

        $this->assertFalse($result->first()->relationLoaded('related'));

        $this->assertEquals([], $model->getRelations());

        $result = QueryBuilder::for($model)
            ->allowedIncludes(['related'])
            ->get();

        $this->assertSame($model, $result);
        $this->assertFalse($result->relationLoaded('related'));
    }
    public function test_allowed_includes_applied()
    {
        $model = $this->createModel();

        $result = QueryBuilder::for(QueryableModel::query(), $this->withIncludes('related'))
            ->allowedIncludes(['related']);

        $result = $result->get();

        $this->assertTrue($result->first()->relationLoaded('related'));

        $this->assertEquals([], $model->getRelations());

        $result = QueryBuilder::for($model, $this->withIncludes('related'))
            ->allowedIncludes(['related'])
            ->get();

        $this->assertSame($model, $result);
        $this->assertTrue($result->relationLoaded('related'));
    }
    public function test_can_set_default_includes()
    {
        $model = $this->createModel();

        $result = QueryBuilder::for(QueryableModel::query())
            ->allowedIncludes([], ['related']);

        $result = $result->get();

        $this->assertTrue($result->first()->relationLoaded('related'));

        $this->assertEquals([], $model->getRelations());

        $result = QueryBuilder::for($model)
            ->allowedIncludes([], ['related'])
            ->get();

        $this->assertSame($model, $result);
        $this->assertTrue($result->relationLoaded('related'));
    }
    public function test_requested_includes_overrides_defaults()
    {
        $model = $this->createModelWithNested();

        $result = QueryBuilder::for(QueryableModel::query(), $this->withIncludes('related2'))
            ->allowedIncludes(['related2'], ['related']);

        $result = $result->get();

        $this->assertFalse($result->first()->relationLoaded('related'));
        $this->assertTrue($result->first()->relationLoaded('related2'));

        $this->assertEquals([], $model->getRelations());

        $result = QueryBuilder::for($model, $this->withIncludes('related2'))
            ->allowedIncludes(['related2'], ['related'])
            ->get();

        $this->assertSame($model, $result);
        $this->assertFalse($result->relationLoaded('related'));
        $this->assertTrue($result->relationLoaded('related2'));
    }
    public function test_can_eager_load_includes_to_model()
    {
        $model = $this->createModelWithRelation();

        $result = QueryBuilder::for(QueryableModel::query())
            ->allowedIncludes([], ['related'])
            ->get();

        $this->assertTrue($result->first()->relationLoaded('related'));

        $this->assertEquals([], $model->getRelations());

        $result = QueryBuilder::for($model)
            ->allowedIncludes([], ['related'])
            ->get();

        $this->assertSame($model, $result);
        $this->assertTrue($result->relationLoaded('related'));
    }

    public function test_nested_includes_not_applied_without_request()
    {
        $model = $this->createModelWithNested();

        $result = QueryBuilder::for(QueryableModel::query())
            ->allowedIncludes(['related.nested']);

        $result = $result->get();

        $this->assertEquals([], $model->getRelations());

        $result = QueryBuilder::for($model)
            ->allowedIncludes(['related.nested']);

        $result = $result->get();

        $this->assertFalse($result->relationLoaded('related'));
    }
    public function test_nested_allowed_includes_applied()
    {
        $model = $this->createModelWithNested();
        $model->related2()->save(new RelatedModel());

        $result = QueryBuilder::for(QueryableModel::query(), $this->withIncludes('related.nested,related2'))
            ->allowedIncludes(['related.nested', 'related2']);

        $result = $result->get();

        $this->assertTrue($result->first()->relationLoaded('related2'));
        $this->assertFalse($result->first()->related2->first()->relationLoaded('nested'));
        $this->assertTrue($result->first()->relationLoaded('related'));
        $this->assertTrue($result->first()->related->first()->relationLoaded('nested'));

        $this->assertEquals([], $model->getRelations());

        $result = QueryBuilder::for($model, $this->withIncludes('related.nested,related2'))
            ->allowedIncludes(['related.nested', 'related2']);

        $result = $result->get();

        $this->assertSame($model, $result);
        $this->assertTrue($result->relationLoaded('related2'));
        $this->assertFalse($result->related2->first()->relationLoaded('nested'));
        $this->assertTrue($result->relationLoaded('related'));
        $this->assertTrue($result->related->first()->relationLoaded('nested'));
    }
    public function test_nested_can_set_default_includes()
    {
        $model = $this->createModelWithNested();
        $related = new RelatedModel();
        $related->nested()->associate(NestedModel::create());
        $related->save();
        $model->related2()->save($related);

        $result = QueryBuilder::for(QueryableModel::query())
            ->allowedIncludes(['related2'], ['related.nested']);

        $result = $result->get();

        $this->assertFalse($result->first()->relationLoaded('related2'));
        $this->assertFalse($result->first()->related2->first()->relationLoaded('nested'));
        $this->assertTrue($result->first()->relationLoaded('related'));
        $this->assertTrue($result->first()->related->first()->relationLoaded('nested'));

        $this->assertEquals([], $model->getRelations());

        $result = QueryBuilder::for($model)
            ->allowedIncludes(['related2'], ['related.nested']);

        $result = $result->get();

        $this->assertSame($model, $result);
        $this->assertFalse($result->relationLoaded('related2'));
        $this->assertFalse($result->related2->first()->relationLoaded('nested'));
        $this->assertTrue($result->relationLoaded('related'));
        $this->assertTrue($result->related->first()->relationLoaded('nested'));
    }
    public function test_nested_requested_includes_overrides_defaults()
    {
        $model = $this->createModelWithNested();
        $related = new RelatedModel();
        $related->nested()->associate(NestedModel::create());
        $related->save();
        $model->related2()->save($related);

        $result = QueryBuilder::for(QueryableModel::query(), $this->withIncludes('related2.nested'))
            ->allowedIncludes(['related2.nested'], ['related.nested']);

        $result = $result->get();

        $this->assertTrue($result->first()->relationLoaded('related2'));
        $this->assertTrue($result->first()->related2->first()->relationLoaded('nested'));
        $this->assertFalse($result->first()->relationLoaded('related'));
        $this->assertFalse($result->first()->related->first()->relationLoaded('nested'));

        $this->assertEquals([], $model->getRelations());

        $result = QueryBuilder::for($model, $this->withIncludes('related2.nested'))
            ->allowedIncludes(['related2.nested'], ['related.nested']);

        $result = $result->get();

        $this->assertSame($model, $result);
        $this->assertTrue($result->relationLoaded('related2'));
        $this->assertTrue($result->related2->first()->relationLoaded('nested'));
        $this->assertFalse($result->relationLoaded('related'));
        $this->assertFalse($result->related->first()->relationLoaded('nested'));
    }

    /*
     * Helpers
     */
    public function withIncludes(string $include)
    {
        return new Request([
            'include' => $include,
        ]);
    }

    public function createModel()
    {
        return QueryableModel::create()->fresh();
    }

    public function createModelWithRelation()
    {
        $model = QueryableModel::create();
        $model->related()->save(new RelatedModel());

        return $model;
    }

    public function createModelWithNested()
    {
        $model = QueryableModel::create();
        $related = new RelatedModel();
        $related->nested()->associate(NestedModel::create());

        $model->related()->save($related);

        return $model;
    }
}
