<?php

namespace Tests\Feature\Macro;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;
use Tests\TestCase;

class JsonResourceMacrosTest extends TestCase
{
    public function test_visible_macro()
    {
        $model = new EloquentBuilderMacroTestModel(['name' => 1, 'desc' => 2, 'title' => 3]);
        $resource = new JsonResourceMacroTest($model);

        // Visible attributes.
        $this->assertEquals(1, $resource->visible('name'));
        $this->assertEquals(2, $resource->visible('desc'));
        $this->assertEquals(3, $resource->visible('title'));
        $this->assertNull($resource->visible('dodo'));

        $this->assertEquals('test', $resource->visible('name', 'test'));
        $this->assertEquals('result from closure', $resource->visible('desc', fn() => 'result from closure'));
        $this->assertEquals('foo', $resource->visible('dodo', 'foo'));

        $model->makeHidden(['name', 'title']);

        // Hidden attributes.
        $this->assertEquals(new MissingValue(), $resource->visible('name', 'other'));
        $this->assertEquals(2, $resource->visible('desc'));
        $this->assertEquals('default', $resource->visible('title', 'other', 'default'));

        $model->makeVisible(['name', 'title']);

        // First parameter is array.
        $this->assertEquals([
            'name' => 1,
            'desc' => 2,
            'title' => 'val',
            'other' => null,
        ], $resource->visible(['name', 'desc', 'title' => 'val', 'other']));

        $this->assertEquals([
            'name' => 1,
            'desc' => 2,
            'title' => 'val',
            'other' => null,
            'merged' => 'bar',
        ], $resource->visible(['name', 'desc', 'title' => 'val', 'other'], ['merged' => 'bar']));

        // Merged parameters are not checked for visibility
        $model->makeHidden(['merged']);

        $this->assertEquals([
            'name' => 1,
            'desc' => 2,
            'title' => 'val',
            'other' => null,
            'merged' => 'bar',
        ], $resource->visible(['name', 'desc', 'title' => 'val', 'other'], ['merged' => 'bar']));
    }
}

class EloquentBuilderMacroTestModel extends Model
{
    protected $fillable = ['name', 'desc', 'title'];

    protected $hidden = ['id'];
}

class JsonResourceMacroTest extends JsonResource
{
}
