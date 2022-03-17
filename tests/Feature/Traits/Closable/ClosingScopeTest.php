<?php

namespace Tests\Feature\Traits\Closable;

use App\Scopes\ClosingScope;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use stdClass;

class ClosingScopeTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testApplyingScopeToABuilder()
    {
        $scope = m::mock(ClosingScope::class . '[extend]');
        $builder = m::mock(EloquentBuilder::class);
        $model = m::mock(Model::class);
        $model->shouldReceive('getQualifiedClosedAtColumn')->once()->andReturn('table.closed_at');
        $builder->shouldReceive('whereNull')->once()->with('table.closed_at');

        $scope->apply($builder, $model);

        // Hide PHPUnit warning.
        $this->assertTrue(true);
    }

    public function testWithClosedExtension()
    {
        $builder = new EloquentBuilder(new BaseBuilder(
            m::mock(ConnectionInterface::class),
            m::mock(Grammar::class),
            m::mock(Processor::class)
        ));
        $scope = m::mock(ClosingScope::class . '[remove]');
        $scope->extend($builder);
        $callback = $builder->getMacro('withClosed');
        $givenBuilder = m::mock(EloquentBuilder::class);
        $givenBuilder->shouldReceive('getModel')->andReturn($model = m::mock(Model::class));
        $givenBuilder->shouldReceive('withoutGlobalScope')->with($scope)->andReturn($givenBuilder);
        $result = $callback($givenBuilder);

        $this->assertEquals($givenBuilder, $result);
    }

    public function testOnlyClosedExtension()
    {
        $builder = new EloquentBuilder(new BaseBuilder(
            m::mock(ConnectionInterface::class),
            m::mock(Grammar::class),
            m::mock(Processor::class)
        ));
        $model = m::mock(Model::class);
        $model->makePartial();
        $scope = m::mock(ClosingScope::class . '[remove]');
        $scope->extend($builder);
        $callback = $builder->getMacro('onlyClosed');
        $givenBuilder = m::mock(EloquentBuilder::class);
        $givenBuilder->shouldReceive('getQuery')->andReturn($query = m::mock(stdClass::class));
        $givenBuilder->shouldReceive('getModel')->andReturn($model);
        $givenBuilder->shouldReceive('withoutGlobalScope')->with($scope)->andReturn($givenBuilder);
        $model->shouldReceive('getQualifiedClosedAtColumn')->andReturn('table.closed_at');
        $givenBuilder->shouldReceive('whereNotNull')->once()->with('table.closed_at');
        $result = $callback($givenBuilder);

        $this->assertEquals($givenBuilder, $result);
    }

    public function testWithoutClosedExtension()
    {
        $builder = new EloquentBuilder(new BaseBuilder(
            m::mock(ConnectionInterface::class),
            m::mock(Grammar::class),
            m::mock(Processor::class)
        ));
        $model = m::mock(Model::class);
        $model->makePartial();
        $scope = m::mock(ClosingScope::class . '[remove]');
        $scope->extend($builder);
        $callback = $builder->getMacro('withoutClosed');
        $givenBuilder = m::mock(EloquentBuilder::class);
        $givenBuilder->shouldReceive('getQuery')->andReturn($query = m::mock(stdClass::class));
        $givenBuilder->shouldReceive('getModel')->andReturn($model);
        $givenBuilder->shouldReceive('withoutGlobalScope')->with($scope)->andReturn($givenBuilder);
        $model->shouldReceive('getQualifiedClosedAtColumn')->andReturn('table.closed_at');
        $givenBuilder->shouldReceive('whereNull')->once()->with('table.closed_at');
        $result = $callback($givenBuilder);

        $this->assertEquals($givenBuilder, $result);
    }
}
