<?php

namespace Tests\Feature\Console\Commands;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DeleteUnverifiedUsersTest extends TestCase
{
    use DatabaseTransactions;

    public function test_cant_delete_verified_users()
    {
        User::factory(5)->create(['updated_at' => now()->subDays(8)]);

        Artisan::call('users:delete-unverified');

        $this->assertDatabaseCount('users', 5);
    }

    public function test_cant_delete_unverified_users_before_week()
    {
        User::factory(5)->unverified()->create(['updated_at' => now()->subDays(6)]);

        Artisan::call('users:delete-unverified');

        $this->assertDatabaseCount('users', 5);
    }

    public function test_can_delete_unverified_users_after_week()
    {
        User::factory(5)->unverified()->create(['updated_at' => now()->subDays(8)]);

        Artisan::call('users:delete-unverified');

        $this->assertDatabaseCount('users', 0);
    }
}
