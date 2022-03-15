<?php

namespace Tests\Feature\Database;

use App\Traits\Closable;
use Illuminate\Support\Carbon;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class ClosableTraitTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testClose()
    {
        $model = m::mock(ClosableTraitStub::class);
        $model->makePartial();
        $model->shouldReceive('save')->once();
        $model->shouldReceive('fireModelEvent')->with('closed', false)->andReturn(true);

        $model->close();

        $this->assertInstanceOf(Carbon::class, $model->closed_at);
    }

    public function testOpen()
    {
        $model = m::mock(ClosableTraitStub::class);
        $model->closed_at = Carbon::now();
        $model->makePartial();
        $model->shouldReceive('save')->once();
        $model->shouldReceive('fireModelEvent')->with('opened', false)->andReturn(true);

        $model->open();

        $this->assertNull($model->closed_at);
    }
}

// @codingStandardsIgnoreStart
class ClosableTraitStub
// @codingStandardsIgnoreEnd
{
    use Closable;

    public $closed_at;
    public $updated_at;
    public $timestamps = true;

    public function newQuery()
    {
        //
    }

    public function getKey()
    {
        return 1;
    }

    public function getKeyName()
    {
        return 'id';
    }

    public function save()
    {
        //
    }

    public function fireModelEvent()
    {
        //
    }

    public function freshTimestamp()
    {
        return Carbon::now();
    }

    public function fromDateTime()
    {
        return 'date-time';
    }

    public function getUpdatedAtColumn()
    {
        return 'updated_at';
    }
}
