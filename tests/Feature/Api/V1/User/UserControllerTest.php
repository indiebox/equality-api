<?php

namespace Tests\Feature\Api\V1\User;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_cant_get_user_unauthorized()
    {
        $response = $this->getJson('/api/v1/user');

        $response->assertUnauthorized();
    }

    public function test_can_get_user()
    {
        $user = User::factory()->unverified()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/user');

        $response
            ->assertOk()
            ->assertJson(function ($json) {
                $json->has('data', function ($json) {
                    $json->hasAll(['id', 'name', 'email']);
                });
            })
            ->assertJsonPath('data.email', $user->email);
    }
}
