<?php

namespace Tests\Feature\Auth;

use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RegisteredUserControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_see_form() {
        $response = $this->get('/register');

        $response
            ->assertOk()
            ->assertViewIs('auth.register');
    }

    public function test_can_register()
    {
        Event::fake();
        $data = [
            'name' => 'test',
            'email' => 'test@mail.ru',
            'password' => '123456',
            'password_confirmation' => '123456',
        ];

        $response = $this->post('/register', $data);

        $response->assertViewIs('auth.registered');
        $this->assertDatabaseHas('users', Arr::only($data, ['email']));
        Event::assertDispatched(Registered::class);
    }
}
