<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_register()
    {
        Event::fake();
        $data = [
            'name' => 'test',
            'email' => 'test@mail.ru',
            'password' => '123456',
            'password_confirmation' => '123456',
        ];

        $response = $this->postJson('api/v1/auth/register', $data);

        $response->assertCreated()
            ->assertJsonPath('data.email', 'test@mail.ru');
        $this->assertDatabaseHas('users', Arr::only($data, ['email']));
        Event::assertDispatched(Registered::class);
    }

    public function test_can_login_with_correct_credentials()
    {
        User::factory()->create(['email' => 'test@mail.ru', 'password' => '123456']);

        $data = [
            'email' => 'test@mail.ru',
            'password' => '123456',
            'device_name' => 'Desktop',
        ];

        $response = $this->postJson('api/v1/auth/login', $data);

        $response->assertOk()
            ->assertJsonPath('data.email', 'test@mail.ru');
    }

    public function test_cant_login_with_incorrect_credentials()
    {
        User::factory()->create(['email' => 'test@mail.ru', 'password' => '654321']);

        $data = [
            'email' => 'test@mail.ru',
            'password' => '123456',
            'device_name' => 'Desktop',
        ];

        $response = $this->postJson('api/v1/auth/login', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrorFor('email');
    }
}
