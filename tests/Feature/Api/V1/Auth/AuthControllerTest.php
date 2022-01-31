<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_cant_login_with_incorrect_credentials()
    {
        User::factory()->create(['email' => 'test@mail.ru', 'password' => '654321']);

        $data = [
            'email' => 'test@mail.ru',
            'password' => '123456',
            'device_name' => 'Desktop',
        ];

        $response = $this->postJson('api/v1/login', $data);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('credentials');
    }

    public function test_can_login_with_correct_credentials()
    {
        User::factory()->create(['email' => 'test@mail.ru', 'password' => '123456']);

        $data = [
            'email' => 'test@mail.ru',
            'password' => '123456',
            'device_name' => 'Desktop',
        ];

        $response = $this->postJson('api/v1/login', $data);

        $response
            ->assertOk()
            ->assertJsonPath('data.email', 'test@mail.ru');
    }

    public function test_token_with_same_name_removed()
    {
        User::factory()->create(['email' => 'test@mail.ru', 'password' => '123456']);

        $data = [
            'email' => 'test@mail.ru',
            'password' => '123456',
            'device_name' => 'Desktop-3100 | Windows',
        ];

        $response = $this->postJson('api/v1/login', $data);

        $response
            ->assertOk()
            ->assertJsonPath('data.email', 'test@mail.ru')
            ->assertJsonStructure(['data', 'token']);
        $this->assertDatabaseCount('personal_access_tokens', 1);

        $response = $this->postJson('api/v1/login', $data);

        $response
            ->assertOk()
            ->assertJsonPath('data.email', 'test@mail.ru')
            ->assertJsonStructure(['data', 'token']);
        $this->assertDatabaseCount('personal_access_tokens', 1);

        $response = $this->postJson('api/v1/login', ['device_name' => '521fdsal | Mac'] + $data);

        $response
            ->assertOk()
            ->assertJsonPath('data.email', 'test@mail.ru')
            ->assertJsonStructure(['data', 'token']);
        $this->assertDatabaseCount('personal_access_tokens', 2);
    }

    public function test_can_logout() {
        User::factory()->create(['email' => 'test@mail.ru', 'password' => '123456']);
        $data = [
            'email' => 'test@mail.ru',
            'password' => '123456',
            'device_name' => 'Desktop',
        ];
        $token = $this->postJson('api/v1/login', $data)->json('token');

        $this->assertDatabaseCount('personal_access_tokens', 1);

        $response = $this->postJson('api/v1/logout', [], ["Authorization" => "Bearer {$token}"]);

        $response->assertNoContent();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
