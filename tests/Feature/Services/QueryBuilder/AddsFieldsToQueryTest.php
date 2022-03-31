<?php

namespace Tests\Feature\Services\QueryBuilder;

use App\Services\QueryBuilder\Contracts\ResourceWithFields as ContractsResourceWithFields;
use App\Services\QueryBuilder\QueryBuilder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use InvalidArgumentException;
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

    protected static function clearTestsData($schema)
    {
        $schema->dropIfExists('models');
        $schema->dropIfExists('related_models');
        $schema->dropIfExists('nested_models');
    }

    /*
     * Tests
     */
    public function test_no_errors_when_collection_is_empty()
    {
        $result = QueryBuilder::for(collect(), $this->withFields(['models' => 'id,name']))
            ->allowedFields(['id', 'name'])
            ->get();

        $this->assertTrue(true);
    }
    public function test_allowed_fields_not_applied_without_request()
    {
        $model = $this->createModel();

        $result = QueryBuilder::for(QueryableModel::query())
            ->allowedFields(['id', 'name'])
            ->get();

        $this->assertEquals([], $result->first()->getHidden());

        $result = QueryBuilder::for($model)
            ->allowedFields(['id', 'name'])
            ->get();

        $this->assertSame($model, $result);
        $this->assertEquals([], $result->getHidden());

        $models = collect([$this->createModel(), $this->createModel()]);
        $result = QueryBuilder::for($models)
            ->allowedFields(['id', 'name'])
            ->get();

        $this->assertEquals([], $result->first()->getHidden());
        $this->assertEquals([], $result->get(1)->getHidden());
    }
    public function test_allowed_fields_applied()
    {
        $model = $this->createModel();

        $result = QueryBuilder::for(QueryableModel::query(), $this->withFields(['models' => 'id,name']))
            ->allowedFields(['id', 'name', 'description', 'timestamp', 'created_at', 'updated_at'])
            ->get();

        $this->assertEquals([
            'description',
            'timestamp',
            'created_at',
            'updated_at',
        ], $result->first()->getHidden());

        $this->assertEquals([], $model->getHidden());

        $result = QueryBuilder::for($model, $this->withFields(['models' => 'id,name']))
            ->allowedFields(['id', 'name', 'description', 'timestamp', 'created_at', 'updated_at'])
            ->get();

        $this->assertSame($model, $result);
        $this->assertEquals([
            'description',
            'timestamp',
            'created_at',
            'updated_at',
        ], $result->getHidden());

        $models = collect([$this->createModel(), $this->createModel()]);
        $result = QueryBuilder::for($models, $this->withFields(['models' => 'id,name']))
            ->allowedFields(['id', 'name', 'description', 'timestamp', 'created_at', 'updated_at'])
            ->get();

        $this->assertEquals([
            'description',
            'timestamp',
            'created_at',
            'updated_at',
        ], $result->first()->getHidden());
        $this->assertEquals([
            'description',
            'timestamp',
            'created_at',
            'updated_at',
        ], $result->get(1)->getHidden());
    }
    public function test_can_set_default_fields()
    {
        $model = $this->createModel();

        $result = QueryBuilder::for(QueryableModel::query())
            ->allowedFields(['id', 'name'], ['id'])
            ->get();

        $this->assertEquals(['name'], $result->first()->getHidden());

        $this->assertEquals([], $model->getHidden());

        $result = QueryBuilder::for($model)
            ->allowedFields(['id', 'name'], ['id'])
            ->get();

        $this->assertSame($model, $result);
        $this->assertEquals(['name'], $result->getHidden());

        $models = collect([$this->createModel(), $this->createModel()]);
        $result = QueryBuilder::for($models)
            ->allowedFields(['id', 'name'], ['id'])
            ->get();

        $this->assertEquals(['name'], $result->get(0)->getHidden());
        $this->assertEquals(['name'], $result->get(1)->getHidden());
    }
    public function test_requested_fields_overrides_defaults()
    {
        $model = $this->createModel();

        $result = QueryBuilder::for(QueryableModel::query(), $this->withFields(['models' => 'name']))
            ->allowedFields(['id', 'name'], ['id'])
            ->get();

        $this->assertEquals(['id'], $result->first()->getHidden());

        $this->assertEquals([], $model->getHidden());

        $result = QueryBuilder::for($model, $this->withFields(['models' => 'name']))
            ->allowedFields(['id', 'name'], ['id'])
            ->get();

        $this->assertSame($model, $result);
        $this->assertEquals(['id'], $result->getHidden());

        $models = collect([$this->createModel(), $this->createModel()]);
        $result = QueryBuilder::for($models, $this->withFields(['models' => 'name']))
            ->allowedFields(['id', 'name'], ['id'])
            ->get();

        $this->assertEquals(['id'], $result->get(0)->getHidden());
        $this->assertEquals(['id'], $result->get(1)->getHidden());
    }

    public function test_can_set_alias_for_parent_model()
    {
        $model = $this->createModel();

        $result = QueryBuilder::for(QueryableModel::query(), $this->withFields(['alias' => 'id,name']))
            ->allowedFields(['id', 'name'], ['id'], 'alias')
            ->get();

        $this->assertEquals([], $result->first()->getHidden());

        $this->assertEquals([], $model->getHidden());

        $result = QueryBuilder::for($model, $this->withFields(['alias' => 'id,name']))
            ->allowedFields(['id', 'name'], ['id'], 'alias')
            ->get();

        $this->assertSame($model, $result);
        $this->assertEquals([], $result->getHidden());

        $result = QueryBuilder::for(
            collect([$this->createModel(), $this->createModel()]),
            $this->withFields(['alias' => 'id,name'])
        )
        ->allowedFields(['id', 'name'], ['id'], 'alias')
        ->get();

        $this->assertEquals([], $result->get(0)->getHidden());
        $this->assertEquals([], $result->get(1)->getHidden());

        $this->expectException(InvalidFieldQuery::class);

        $result = QueryBuilder::for(QueryableModel::query(), $this->withFields(['models' => 'id,name']))
            ->allowedFields(['id', 'name'], ['id'], 'alias');
    }

    // Relations
    public function test_related_allowed_fields_not_applied_on_nullable_relation()
    {
        $model = $this->createModel();

        $result = QueryBuilder::for(QueryableModel::with('related'))
            ->allowedFields(['related.id', 'related.name'], ['related.id'])
            ->get();

        $this->assertEquals(0, $result->first()->related->count());

        $model->load('related');
        $result = QueryBuilder::for($model)
            ->allowedFields(['related.id', 'related.name'], ['related.id'])
            ->get();

        $this->assertSame($model, $result);
        $this->assertEquals(0, $result->first()->related->count());

        $result = QueryBuilder::for(
            collect([$this->createModel()->load('related'), $this->createModel()->load('related')])
        )
        ->allowedFields(['related.id', 'related.name'], ['related.id'])
        ->get();

        $this->assertEquals(0, $result->get(0)->related->count());
        $this->assertEquals(0, $result->get(1)->related->count());
    }
    public function test_related_allowed_fields_not_applied_without_request()
    {
        $model = $this->createModelWithRelation();

        $result = QueryBuilder::for(QueryableModel::with('related'))
            ->allowedFields(['related.id', 'related.name'])
            ->get();

        $this->assertEquals([], $result->first()->related->first()->getHidden());

        $model->load('related');
        $result = QueryBuilder::for($model)
            ->allowedFields(['related.id', 'related.name'])
            ->get();

        $this->assertSame($model, $result);
        $this->assertEquals([], $result->related->first()->getHidden());

        $result = QueryBuilder::for(
            collect([
                $this->createModelWithRelation()->load('related'),
                $this->createModelWithRelation()->load('related'),
            ])
        )
        ->allowedFields(['related.id', 'related.name'])
        ->get();

        $this->assertEquals([], $result->get(0)->related->first()->getHidden());
        $this->assertEquals([], $result->get(1)->related->first()->getHidden());
    }
    public function test_related_allowed_fields_applied()
    {
        $model = $this->createModelWithRelation();

        $result = QueryBuilder::for(QueryableModel::with('related'), $this->withFields(['related' => 'id,name']))
            ->allowedFields(['related.id', 'related.name'])
            ->get();

        $this->assertEquals([], $result->first()->related->first()->getHidden());

        $this->assertEquals([], $model->getHidden());

        $model->load('related');
        $result = QueryBuilder::for($model, $this->withFields(['related' => 'id,name']))
            ->allowedFields(['related.id', 'related.name'])
            ->get();

        $this->assertSame($model, $result);
        $this->assertEquals([], $result->related->first()->getHidden());

        $result = QueryBuilder::for(
            collect([
                $this->createModelWithRelation()->load('related'),
                $this->createModelWithRelation()->load('related'),
            ]),
            $this->withFields(['related' => 'id,name'])
        )
        ->allowedFields(['related.id', 'related.name'])
        ->get();

        $this->assertEquals([], $result->get(0)->related->first()->getHidden());
        $this->assertEquals([], $result->get(1)->related->first()->getHidden());
    }
    public function test_related_can_set_default_fields()
    {
        $model = $this->createModelWithRelation();

        $result = QueryBuilder::for(QueryableModel::with('related'))
            ->allowedFields(['related.id', 'related.name'], ['related.id'])
            ->get();

        $this->assertEquals(['name'], $result->first()->related->first()->getHidden());

        $this->assertEquals([], $model->getHidden());

        $model->load('related');
        $result = QueryBuilder::for($model)
            ->allowedFields(['related.id', 'related.name'], ['related.id'])
            ->keepRelations()
            ->get();

        $this->assertSame($model, $result);
        $this->assertEquals(['name'], $result->related->first()->getHidden());

        $result = QueryBuilder::for(
            collect([
                $this->createModelWithRelation()->load('related'),
                $this->createModelWithRelation()->load('related'),
            ])
        )
        ->allowedFields(['related.id', 'related.name'], ['related.id'])
        ->keepRelations()
        ->get();

        $this->assertEquals(['name'], $result->get(0)->related->first()->getHidden());
        $this->assertEquals(['name'], $result->get(1)->related->first()->getHidden());
    }
    public function test_related_requested_fields_overrides_defaults()
    {
        $model = $this->createModelWithRelation();

        $result = QueryBuilder::for(QueryableModel::with('related'), $this->withFields(['related' => 'name']))
            ->allowedFields(['related.id', 'related.name'], ['related.id'])
            ->get();

        $this->assertEquals(['id'], $result->first()->related->first()->getHidden());

        $this->assertEquals([], $model->getHidden());

        $model->load('related');
        $result = QueryBuilder::for($model, $this->withFields(['related' => 'name']))
            ->allowedFields(['related.id', 'related.name'], ['related.id'])
            ->keepRelations()
            ->get();

        $this->assertSame($model, $result);
        $this->assertEquals(['id'], $result->related->first()->getHidden());

        $result = QueryBuilder::for(
            collect([
                $this->createModelWithRelation()->load('related'),
                $this->createModelWithRelation()->load('related'),
            ]),
            $this->withFields(['related' => 'name'])
        )
        ->allowedFields(['related.id', 'related.name'], ['related.id'])
        ->keepRelations()
        ->get();

        $this->assertEquals(['id'], $result->get(0)->related->first()->getHidden());
        $this->assertEquals(['id'], $result->get(1)->related->first()->getHidden());
    }

    // Nested relations
    public function test_nested_allowed_fields_not_applied_on_nullable_relation()
    {
        $model = $this->createModelWithRelation();

        $result = QueryBuilder::for(QueryableModel::with('related.nested'))
            ->allowedFields([], ['related.nested.id'])
            ->get();

        $this->assertNull($result->first()->related[0]->nested);

        $model->load('related.nested');
        $result = QueryBuilder::for($model)
            ->allowedFields([], ['related.nested.id'])
            ->get();

        $this->assertSame($model, $result);
        $this->assertNull($result->first()->related[0]->nested);

        $result = QueryBuilder::for(
            collect([
                $this->createModelWithRelation()->load('related.nested'),
                $this->createModelWithRelation()->load('related.nested'),
            ])
        )
        ->allowedFields([], ['related.nested.id'])
        ->get();

        $this->assertNull($result->get(0)->related[0]->nested);
        $this->assertNull($result->get(1)->related[0]->nested);
    }
    public function test_nested_allowed_fields_not_applied_without_request()
    {
        $model = $this->createModelWithNested();

        $result = QueryBuilder::for(QueryableModel::with('related.nested'))
            ->allowedFields(['related.nested.id', 'related.nested.name'])
            ->get();

        $this->assertEquals([], $result->first()->related->first()->nested->getHidden());

        $model->load('related.nested');
        $result = QueryBuilder::for($model)
            ->allowedFields(['related.nested.id', 'related.nested.name'])
            ->get();

        $this->assertSame($model, $result);
        $this->assertEquals([], $result->related->first()->nested->getHidden());

        $result = QueryBuilder::for(
            collect([
                $this->createModelWithNested()->load('related.nested'),
                $this->createModelWithNested()->load('related.nested'),
            ])
        )
        ->allowedFields(['related.nested.id', 'related.nested.name'])
        ->get();

        $this->assertEquals([], $result->get(0)->related[0]->nested->getHidden());
        $this->assertEquals([], $result->get(1)->related[0]->nested->getHidden());
    }
    public function test_nested_allowed_fields_applied()
    {
        $model = $this->createModelWithNested();

        $result = QueryBuilder::for(QueryableModel::with('related.nested'), $this->withFields(['related.nested' => 'id,name']))
            ->allowedFields(['related.nested.id', 'related.nested.name'])
            ->get();

        $this->assertEquals([], $result->first()->related->first()->nested->getHidden());

        $this->assertEquals([], $model->getHidden());

        $model->load('related.nested');
        $result = QueryBuilder::for($model, $this->withFields(['related.nested' => 'id,name']))
            ->allowedFields(['related.nested.id', 'related.nested.name'])
            ->get();

        $this->assertSame($model, $result);
        $this->assertEquals([], $result->related->first()->nested->getHidden());

        $result = QueryBuilder::for(
            collect([
                $this->createModelWithNested()->load('related.nested'),
                $this->createModelWithNested()->load('related.nested'),
            ]),
            $this->withFields(['related.nested' => 'id,name'])
        )
        ->allowedFields(['related.nested.id', 'related.nested.name'])
        ->get();

        $this->assertEquals([], $result->get(0)->related[0]->nested->getHidden());
        $this->assertEquals([], $result->get(1)->related[0]->nested->getHidden());
    }
    public function test_nested_can_set_default_fields()
    {
        $model = $this->createModelWithNested();

        $result = QueryBuilder::for(QueryableModel::with('related.nested'))
            ->allowedFields(['related.nested.id', 'related.nested.name'], ['related.nested.id'])
            ->get();

        $this->assertEquals(['name'], $result->first()->related->first()->nested->getHidden());

        $this->assertEquals([], $model->getHidden());

        $model->load('related.nested');
        $result = QueryBuilder::for($model)
            ->allowedFields(['related.nested.id', 'related.nested.name'], ['related.nested.id'])
            ->keepRelations()
            ->get();

        $this->assertSame($model, $result);
        $this->assertEquals(['name'], $result->related->first()->nested->getHidden());

        $result = QueryBuilder::for(
            collect([
                $this->createModelWithNested()->load('related.nested'),
                $this->createModelWithNested()->load('related.nested'),
            ])
        )
        ->allowedFields(['related.nested.id', 'related.nested.name'], ['related.nested.id'])
        ->keepRelations()
        ->get();

        $this->assertEquals(['name'], $result->get(0)->related[0]->nested->getHidden());
        $this->assertEquals(['name'], $result->get(1)->related[0]->nested->getHidden());
    }
    public function test_nested_requested_fields_overrides_defaults()
    {
        $model = $this->createModelWithNested();

        $result = QueryBuilder::for(QueryableModel::with('related.nested'), $this->withFields(['related.nested' => 'name']))
            ->allowedFields(['related.nested.id', 'related.nested.name'], ['related.nested.id'])
            ->get();

        $this->assertEquals(['id'], $result->first()->related->first()->nested->getHidden());

        $this->assertEquals([], $model->getHidden());

        $model->load('related.nested');
        $result = QueryBuilder::for($model, $this->withFields(['related.nested' => 'name']))
            ->allowedFields(['related.nested.id', 'related.nested.name'], ['related.nested.id'])
            ->keepRelations()
            ->get();

        $this->assertSame($model, $result);
        $this->assertEquals(['id'], $result->related->first()->nested->getHidden());

        $result = QueryBuilder::for(
            collect([
                $this->createModelWithNested()->load('related.nested'),
                $this->createModelWithNested()->load('related.nested'),
            ]),
            $this->withFields(['related.nested' => 'name'])
        )
        ->allowedFields(['related.nested.id', 'related.nested.name'], ['related.nested.id'])
        ->keepRelations()
        ->get();

        $this->assertEquals(['id'], $result->get(0)->related[0]->nested->getHidden());
        $this->assertEquals(['id'], $result->get(1)->related[0]->nested->getHidden());
    }

    // Pass objects that implements ResourceWithFields
    public function test_cant_use_object_that_not_implement_contract()
    {
        $this->expectException(InvalidArgumentException::class);

        QueryBuilder::for(QueryableModel::query())
            ->allowedFields([
                get_class(new class {
                }),
            ]);
    }
    public function test_can_set_alias_for_object()
    {
        $model = $this->createModel();

        $result = QueryBuilder::for(QueryableModel::query(), $this->withFields(['alias' => 'id,name']))
            ->allowedFields([ResourceWithFields::class => 'alias'], [], 'alias')
            ->get();

        $this->assertEquals([], $result->first()->getHidden());

        $result = QueryBuilder::for($model, $this->withFields(['alias' => 'id,name']))
            ->allowedFields([ResourceWithFields::class => 'alias'], [], 'alias')
            ->get();

        $this->assertSame($model, $result);
        $this->assertEquals([], $result->getHidden());

        $result = QueryBuilder::for(
            collect([
                $this->createModel(),
                $this->createModel(),
            ]),
            $this->withFields(['alias' => 'id,name'])
        )
        ->allowedFields([ResourceWithFields::class => 'alias'], [], 'alias')
        ->get();

        $this->assertEquals([], $result->get(0)->getHidden());
        $this->assertEquals([], $result->get(1)->getHidden());
    }
    public function test_can_set_multiple_aliases_for_object()
    {
        $this->expectExceptionMessage("Requested field(s) `alias.id, alias.name` are not allowed."
        . " Allowed field(s) are `alias1.id, alias1.name, alias2.id, alias2.name`.");

        QueryBuilder::for(QueryableModel::query(), $this->withFields(['alias' => 'id,name']))
            ->allowedFields([ResourceWithFields::class => ['alias1', 'alias2']], []);
    }
    public function test_can_set_multiple_aliases_for_object_for_model()
    {
        $model = $this->createModel();

        $this->expectExceptionMessage("Requested field(s) `alias.id, alias.name` are not allowed."
        . " Allowed field(s) are `alias1.id, alias1.name, alias2.id, alias2.name`.");

        QueryBuilder::for($model, $this->withFields(['alias' => 'id,name']))
            ->allowedFields([ResourceWithFields::class => ['alias1', 'alias2']], []);
    }
    public function test_can_use_default_alias_for_object()
    {
        $model = $this->createModel();

        $result = QueryBuilder::for(QueryableModel::query(), $this->withFields(['related' => 'id,name']))
            ->allowedFields([NestedResourceWithFields::class], [], 'related')
            ->get();

        $this->assertEquals(['desc'], $result->first()->getHidden());

        $result = QueryBuilder::for($model, $this->withFields(['related' => 'id,name']))
            ->allowedFields([NestedResourceWithFields::class], [], 'related')
            ->get();

        $this->assertSame($model, $result);
        $this->assertEquals(['desc'], $result->getHidden());

        $result = QueryBuilder::for(
            collect([
                $this->createModel(),
                $this->createModel(),
            ]),
            $this->withFields(['related' => 'id,name'])
        )
        ->allowedFields([NestedResourceWithFields::class], [], 'related')
        ->get();

        $this->assertEquals(['desc'], $result->get(0)->getHidden());
        $this->assertEquals(['desc'], $result->get(1)->getHidden());
    }
    public function test_alias_for_object_overrides_default()
    {
        $model = $this->createModel();

        $result = QueryBuilder::for(QueryableModel::query(), $this->withFields(['override' => 'id,name']))
            ->allowedFields([NestedResourceWithFields::class => 'override'], [], 'override')
            ->get();

        $this->assertEquals(['desc'], $result->first()->getHidden());

        $result = QueryBuilder::for($model, $this->withFields(['override' => 'id,name']))
            ->allowedFields([NestedResourceWithFields::class => 'override'], [], 'override')
            ->get();

        $this->assertSame($model, $result);
        $this->assertEquals(['desc'], $result->getHidden());

        $result = QueryBuilder::for(
            collect([
                $this->createModel(),
                $this->createModel(),
            ]),
            $this->withFields(['override' => 'id,name'])
        )
        ->allowedFields([NestedResourceWithFields::class => 'override'], [], 'override')
        ->get();

        $this->assertEquals(['desc'], $result->get(0)->getHidden());
        $this->assertEquals(['desc'], $result->get(1)->getHidden());
    }
    public function test_can_return_assoc_array_in_object()
    {
        $model = $this->createModel();

        $class = get_class(new class implements ContractsResourceWithFields {
            public static function defaultName(): string
            {
                return "related";
            }

            public static function defaultFields(): array
            {
                return [
                    'id' => 'test',
                ];
            }

            public static function allowedFields(): array
            {
                return [
                    'id',
                    'name' => 'test',
                    'desc',
                ];
            }
        });

        $result = QueryBuilder::for(QueryableModel::query(), $this->withFields(['related' => 'id,name']))
            ->allowedFields([$class], [], 'related')
            ->get();

        $this->assertEquals(['desc'], $result->first()->getHidden());

        $result = QueryBuilder::for($model, $this->withFields(['related' => 'id,name']))
            ->allowedFields([$class], [], 'related')
            ->get();

        $this->assertSame($model, $result);
        $this->assertEquals(['desc'], $result->getHidden());

        $result = QueryBuilder::for(
            collect([$this->createModel(), $this->createModel()]),
            $this->withFields(['related' => 'id,name'])
        )
        ->allowedFields([$class], [], 'related')
        ->get();

        $this->assertEquals(['desc'], $result->get(0)->getHidden());
        $this->assertEquals(['desc'], $result->get(1)->getHidden());
    }
    public function test_can_use_multiple_objects()
    {
        $this->expectExceptionMessage("Requested field(s) `override.non-existing` are not allowed. "
            . "Allowed field(s) are `models.id, models.name, nested.id, nested.name, nested.desc`.");

        QueryBuilder::for(QueryableModel::query(), $this->withFields(['override' => 'non-existing']))
            ->allowedFields([
                ResourceWithFields::class,
                NestedResourceWithFields::class => 'nested',
            ], [
                ResourceWithFields::class,
                NestedResourceWithFields::class => 'nested',
            ]);
    }
    public function test_can_use_multiple_objects_for_model()
    {
        $model = $this->createModel();

        $this->expectExceptionMessage("Requested field(s) `override.non-existing` are not allowed. "
            . "Allowed field(s) are `override.id, override.name, nested.id, nested.name, nested.desc`.");

        QueryBuilder::for($model, $this->withFields(['override' => 'non-existing']))
            ->allowedFields([
                ResourceWithFields::class,
                NestedResourceWithFields::class => 'nested',
            ], [
                ResourceWithFields::class,
                NestedResourceWithFields::class => 'nested',
            ], 'override');
    }
    public function test_allowed_fields_not_applied_without_request_using_object()
    {
        $model = $this->createModel();

        $result = QueryBuilder::for(QueryableModel::query())
            ->allowedFields([ResourceWithFields::class])
            ->get();

        $this->assertEquals([], $result->first()->getHidden());

        $result = QueryBuilder::for($model)
            ->allowedFields([ResourceWithFields::class])
            ->get();

        $this->assertSame($model, $result);
        $this->assertEquals([], $result->first()->getHidden());

        $result = QueryBuilder::for(
            collect([$this->createModel(), $this->createModel()])
        )
        ->allowedFields([ResourceWithFields::class])
        ->get();

        $this->assertEquals([], $result->get(0)->getHidden());
        $this->assertEquals([], $result->get(1)->getHidden());
    }
    public function test_allowed_fields_applied_using_object()
    {
        $model = $this->createModel();

        $result = QueryBuilder::for(QueryableModel::query(), $this->withFields(['models' => 'id,name']))
            ->allowedFields([ResourceWithFields::class])
            ->get();

        $this->assertEquals([], $result->first()->getHidden());

        $this->assertEquals([], $model->getHidden());

        $result = QueryBuilder::for($model, $this->withFields(['models' => 'id,name']))
            ->allowedFields([ResourceWithFields::class])
            ->get();

        $this->assertSame($model, $result);
        $this->assertEquals([], $result->getHidden());

        $result = QueryBuilder::for(
            collect([$this->createModel(), $this->createModel()]),
            $this->withFields(['models' => 'id,name'])
        )
        ->allowedFields([ResourceWithFields::class], [ResourceWithFields::class])
        ->get();

        $this->assertEquals([], $result->get(0)->getHidden());
        $this->assertEquals([], $result->get(1)->getHidden());
    }
    public function test_can_set_default_fields_using_object()
    {
        $model = $this->createModel();

        $result = QueryBuilder::for(QueryableModel::query())
            ->allowedFields([ResourceWithFields::class], [ResourceWithFields::class])
            ->get();

        $this->assertEquals(['name'], $result->first()->getHidden());

        $this->assertEquals([], $model->getHidden());

        $result = QueryBuilder::for($model)
            ->allowedFields([ResourceWithFields::class], [ResourceWithFields::class])
            ->get();

        $this->assertSame($model, $result);
        $this->assertEquals(['name'], $result->getHidden());

        $result = QueryBuilder::for(collect([$this->createModel(), $this->createModel()]))
            ->allowedFields([ResourceWithFields::class], [ResourceWithFields::class])
            ->get();

        $this->assertEquals(['name'], $result->get(0)->getHidden());
        $this->assertEquals(['name'], $result->get(1)->getHidden());
    }
    public function test_requested_fields_overrides_defaults_using_object()
    {
        $model = $this->createModel();

        $result = QueryBuilder::for(QueryableModel::query(), $this->withFields(['models' => 'name']))
            ->allowedFields([ResourceWithFields::class], [ResourceWithFields::class])
            ->get();

        $this->assertEquals(['id'], $result->first()->getHidden());

        $this->assertEquals([], $model->getHidden());

        $result = QueryBuilder::for($model, $this->withFields(['models' => 'name']))
            ->allowedFields([ResourceWithFields::class], [ResourceWithFields::class])
            ->get();

        $this->assertSame($model, $result);
        $this->assertEquals(['id'], $result->getHidden());

        $result = QueryBuilder::for(
            collect([$this->createModel(), $this->createModel()]),
            $this->withFields(['models' => 'name'])
        )
        ->allowedFields([ResourceWithFields::class], [ResourceWithFields::class])
        ->get();

        $this->assertEquals(['id'], $result->get(0)->getHidden());
        $this->assertEquals(['id'], $result->get(1)->getHidden());
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

class ResourceWithFields implements ContractsResourceWithFields
{
    public static function defaultName(): string
    {
        return "";
    }

    public static function defaultFields(): array
    {
        return ['id'];
    }

    public static function allowedFields(): array
    {
        return ['id', 'name'];
    }
}

class NestedResourceWithFields implements ContractsResourceWithFields
{
    public static function defaultName(): string
    {
        return "related";
    }

    public static function defaultFields(): array
    {
        return ['id'];
    }

    public static function allowedFields(): array
    {
        return ['id', 'name', 'desc'];
    }
}
