<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_cant_get_user()
    {
        $response = $this->getJson('/api/v1/user');

        $response->assertUnauthorized();
    }

    public function test_can_get_user_with_token()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/api/v1/user');

        $response->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }
}
