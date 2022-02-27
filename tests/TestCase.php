<?php

namespace Tests;

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
        // We migrate our database before setuping other traits,
        // so database migration will not be included in transactions that are used by traits.
        $this->setupDatabase();

        return parent::setUpTraits();
    }

    /**
     * Setup database for tests.
     */
    protected function setupDatabase()
    {
        Artisan::call('migrate');
    }
}
