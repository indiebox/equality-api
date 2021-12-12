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

        $this->setupDatabase();
    }

    /**
     * Setup database for tests.
     */
    protected function setupDatabase()
    {
        Artisan::call('migrate');
    }
}
