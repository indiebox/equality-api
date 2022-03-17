<?php

namespace Tests\Feature\Traits\Closable;

use App\Traits\Closable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class ClosingTest extends TestCase
{
    public function testClosedAtIsAddedToCastsAsDefaultType()
    {
        $model = new ClosingModel();

        $this->assertArrayHasKey('closed_at', $model->getCasts());
        $this->assertSame('datetime', $model->getCasts()['closed_at']);
    }

    public function testClosedAtIsCastToCarbonInstance()
    {
        $expected = Carbon::createFromFormat('Y-m-d H:i:s', '2018-12-29 13:59:39');
        $model = new ClosingModel(['closed_at' => $expected->format('Y-m-d H:i:s')]);

        $this->assertInstanceOf(Carbon::class, $model->closed_at);
        $this->assertTrue($expected->eq($model->closed_at));
    }

    public function testExistingCastOverridesAddedDateCast()
    {
        $model = new class (['closed_at' => '2018-12-29 13:59:39']) extends ClosingModel
        {
            protected $casts = ['closed_at' => 'bool'];
        };

        $this->assertTrue($model->closed_at);
    }

    public function testExistingMutatorOverridesAddedDateCast()
    {
        $model = new class (['closed_at' => '2018-12-29 13:59:39']) extends ClosingModel
        {
            protected function getClosedAtAttribute()
            {
                return 'expected';
            }
        };

        $this->assertSame('expected', $model->closed_at);
    }

    public function testCastingToStringOverridesAutomaticDateCastingToRetainPreviousBehaviour()
    {
        $model = new class (['closed_at' => '2018-12-29 13:59:39']) extends ClosingModel
        {
            protected $casts = ['closed_at' => 'string'];
        };

        $this->assertSame('2018-12-29 13:59:39', $model->closed_at);
    }
}

class ClosingModel extends Model
{
    use Closable;

    protected $guarded = [];

    protected $dateFormat = 'Y-m-d H:i:s';
}
