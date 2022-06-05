<?php

namespace Tests;

use App\Http\Kernel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\ParallelTesting;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

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
        Artisan::call('db:seed --class=ColumnTypeSeeder');
    }

    /**
     * Clear all tests data.
     *
     * This method called after each test of class has been completed.
     * This is the great place for remove some tables, etc.
     *
     * @param \Illuminate\Database\Schema\Builder $schema
     */
    protected static function clearTestsData($schema)
    {
    }

    public static function tearDownAfterClass(): void
    {
        $app = require __DIR__ . '/../bootstrap/app.php';
        $app = $app->make(Kernel::class);
        $app->bootstrap();

        // Tests runs in parallel...
        if ($token = ParallelTesting::token()) {
            $connection = config('database.default');
            $databaseName = config("database.connections.{$connection}.database");

            config(["database.connections.{$connection}.database" => $databaseName . '_test_' . $token]);
        }

        $schema = Model::getConnectionResolver()->connection()->getSchemaBuilder();

        static::clearTestsData($schema);
    }
}
