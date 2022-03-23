<?php

namespace Tests\Feature\Traits\HasOrder;

use App\Traits\HasOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EloquentHasOrderIntegrationTest extends TestCase
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
            $table->integer('group_id')->nullable();
            $table->decimal('order', 6, 1)->nullable();
            $table->timestamps();
        });
    }

    protected static function clearTestsData($schema)
    {
        $schema->dropIfExists('models');
    }

    /**
     * Tests
     */
    public function test_trait_defines_cast()
    {
        $model = new OrderableModel();

        $this->assertEquals('decimal:1', $model->getCasts()['order']);
    }

    public function test_order_by_position_scope()
    {
        $this->createModels();

        $models = OrderableModel::orderByPosition()->get();

        $this->assertCount(3, $models);
        $this->assertEquals(1, $models->get(0)->order);
        $this->assertEquals(2, $models->get(1)->order);
        $this->assertEquals(3, $models->get(2)->order);
    }

    public function test_order_by_position_desc_scope()
    {
        $this->createModels();

        $models = OrderableModel::orderByPositionDesc()->get();

        $this->assertCount(3, $models);
        $this->assertEquals(3, $models->get(0)->order);
        $this->assertEquals(2, $models->get(1)->order);
        $this->assertEquals(1, $models->get(2)->order);
    }

    public function test_move_to_start_method()
    {
        $models = $this->createModels();
        $models[2]->moveToStart();

        $this->assertEquals(0.9, $models[2]->order);
        $this->assertEquals(1, $models[0]->order);
        $this->assertEquals(2, $models[1]->order);

        $this->refreshModels($models);

        $this->assertEquals(1, $models[2]->order);
        $this->assertEquals(2, $models[0]->order);
        $this->assertEquals(3, $models[1]->order);
    }

    public function test_move_to_end_method()
    {
        $models = $this->createModels();
        $models[0]->moveToEnd();
        $this->refreshModels($models);

        $this->assertEquals(2, $models[1]->order);
        $this->assertEquals(3, $models[2]->order);
        $this->assertEquals(4, $models[0]->order);
    }

    public function test_move_after_method_up()
    {
        $models = $this->createModels();
        $models[2]->moveAfter($models[0]);

        $this->assertEquals(1, $models[0]->order);
        $this->assertEquals(1.1, $models[2]->order);
        $this->assertEquals(2, $models[1]->order);

        $this->refreshModels($models);

        $this->assertEquals(1, $models[0]->order);
        $this->assertEquals(2, $models[2]->order);
        $this->assertEquals(3, $models[1]->order);
    }
    public function test_move_after_method_bottom()
    {
        $models = $this->createModels();
        $models[0]->moveAfter($models[1]);

        $this->assertEquals(2, $models[1]->order);
        $this->assertEquals(2.1, $models[0]->order);
        $this->assertEquals(3, $models[2]->order);

        $this->refreshModels($models);

        $this->assertEquals(1, $models[1]->order);
        $this->assertEquals(2, $models[0]->order);
        $this->assertEquals(3, $models[2]->order);
    }
    public function test_move_after_method_other_type()
    {
        $models = $this->createModels();
        $result = $models[0]->moveAfter(new class extends OrderableModel {
        });

        $this->assertFalse($result);
    }

    public function test_move_to_method()
    {
        $models = $this->createModels();
        $models[0]->moveTo(null);

        $this->assertEquals(2, $models[1]->order);
        $this->assertEquals(3, $models[2]->order);
        $this->assertEquals(4, $models[0]->order);

        $this->refreshModels($models);

        $this->assertEquals(2, $models[1]->order);
        $this->assertEquals(3, $models[2]->order);
        $this->assertEquals(4, $models[0]->order);

        $models[0]->moveTo(0);

        $this->assertEquals(0.9, $models[0]->order);
        $this->assertEquals(2, $models[1]->order);
        $this->assertEquals(3, $models[2]->order);

        $this->refreshModels($models);

        $this->assertEquals(1, $models[0]->order);
        $this->assertEquals(2, $models[1]->order);
        $this->assertEquals(3, $models[2]->order);

        $models[0]->moveTo($models[1]);

        $this->assertEquals(2, $models[1]->order);
        $this->assertEquals(2.1, $models[0]->order);
        $this->assertEquals(3, $models[2]->order);

        $this->refreshModels($models);

        $this->assertEquals(1, $models[1]->order);
        $this->assertEquals(2, $models[0]->order);
        $this->assertEquals(3, $models[2]->order);
    }

    public function test_move_in_one_group_doesnt_affect_other_group()
    {
        $otherModels = $this->createModels();
        $models = $this->createModels(2);
        $models[0]->moveToEnd();
        $this->refreshModels(array_merge($otherModels, $models));

        $this->assertEquals(1, $otherModels[0]->order);
        $this->assertEquals(2, $otherModels[1]->order);
        $this->assertEquals(3, $otherModels[2]->order);
        $this->assertEquals(2, $models[1]->order);
        $this->assertEquals(3, $models[2]->order);
        $this->assertEquals(4, $models[0]->order);

        $models[0]->moveToStart();
        $this->refreshModels(array_merge($otherModels, $models));

        $this->assertEquals(1, $otherModels[0]->order);
        $this->assertEquals(2, $otherModels[1]->order);
        $this->assertEquals(3, $otherModels[2]->order);
        $this->assertEquals(1, $models[0]->order);
        $this->assertEquals(2, $models[1]->order);
        $this->assertEquals(3, $models[2]->order);

        $models[0]->moveAfter($models[1]);
        $this->refreshModels(array_merge($otherModels, $models));

        $this->assertEquals(1, $otherModels[0]->order);
        $this->assertEquals(2, $otherModels[1]->order);
        $this->assertEquals(3, $otherModels[2]->order);
        $this->assertEquals(1, $models[1]->order);
        $this->assertEquals(2, $models[0]->order);
        $this->assertEquals(3, $models[2]->order);
    }

    /*
     * Helpers
     */
    public function createModels($group = 1)
    {
        $models = [];

        $models[] = OrderableModel::create(['group_id' => $group, 'order' => 1]);
        $models[] = OrderableModel::create(['group_id' => $group, 'order' => 2]);
        $models[] = OrderableModel::create(['group_id' => $group, 'order' => 3]);

        return $models;
    }

    public function refreshModels($models)
    {
        foreach ($models as $model) {
            $model->refresh();
        }
    }
}

class OrderableModel extends Model
{
    use HasOrder;

    protected $table = 'models';

    protected $fillable = ['group_id', 'order'];

    public function getOrderQuery($query)
    {
        $query->where('group_id', $this->group_id);
    }
}
