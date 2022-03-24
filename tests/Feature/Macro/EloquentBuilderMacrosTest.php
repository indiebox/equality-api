<?php

namespace Tests\Feature\Macro;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\MissingValue;
use Tests\TestCase;

class EloquentBuilderMacrosTest extends TestCase
{
    public function test_visible_macro()
    {
        $model = new EloquentModel(['name' => 1, 'desc' => 2, 'title' => 3]);

        // Visible attributes.
        $this->assertEquals(1, $model->visible('name'));
        $this->assertEquals(2, $model->visible('desc'));
        $this->assertEquals(3, $model->visible('title'));
        $this->assertNull($model->visible('dodo'));

        $this->assertEquals('test', $model->visible('name', 'test'));
        $this->assertEquals('result from closure', $model->visible('desc', fn() => 'result from closure'));
        $this->assertEquals('foo', $model->visible('dodo', 'foo'));

        $model->makeHidden(['name', 'title']);

        // Hidden attributes.
        $this->assertEquals(new MissingValue(), $model->visible('name', 'other'));
        $this->assertEquals(2, $model->visible('desc'));
        $this->assertEquals('default', $model->visible('title', 'other', 'default'));

        $model->makeVisible(['name', 'title']);

        // First parameter is array.
        $this->assertEquals([
            'name' => 1,
            'desc' => 2,
            'title' => 'val',
            'other' => null,
        ], $model->visible(['name', 'desc', 'title' => 'val', 'other']));

        $this->assertEquals([
            'name' => 1,
            'desc' => 2,
            'title' => 'val',
            'other' => null,
            'merged' => 'bar',
        ], $model->visible(['name', 'desc', 'title' => 'val', 'other'], ['merged' => 'bar']));

        // Merged parameters are not checked for visibility
        $model->makeHidden(['merged']);

        $this->assertEquals([
            'name' => 1,
            'desc' => 2,
            'title' => 'val',
            'other' => null,
            'merged' => 'bar',
        ], $model->visible(['name', 'desc', 'title' => 'val', 'other'], ['merged' => 'bar']));
    }
}

class EloquentModel extends Model
{
    protected $fillable = ['name', 'desc', 'title'];

    protected $hidden = ['id'];
}
