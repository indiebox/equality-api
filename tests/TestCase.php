<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Boot the testing helper traits.
     *
     * @return array
     */
    protected function setUpTraits()
    {
        $schema = Model::getConnectionResolver()->connection()->getSchemaBuilder();

        // We migrate our database before setuping other traits,
        // so database migration will not be included in transactions that are used by traits.
        $this->setupDatabase($schema);

        return parent::setUpTraits();
    }

    /**
     * Setup database for tests.
     *
     * We can override this method in tests to setup some tables
     * for test class, but we need to also call parent method.
     *
     * @param \Illuminate\Database\Schema\Builder $schema
     */
    protected function setupDatabase($schema)
    {
        Artisan::call('migrate');
    }
}
