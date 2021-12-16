<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Listeners\InvalidateUserTokens;
use App\Models\User;
use App\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ResetPasswordControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_reset_password_link_can_be_sent()
    {
        Notification::fake();
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/forgot-password', ['email' => $user->email]);

        $response
            ->assertOk()
            ->assertJsonStructure(['message']);
        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_password_can_be_reset_with_valid_token()
    {
        Event::fake();
        Notification::fake();
        $user = User::factory()->create();

        $this->postJson('/api/v1/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
            $response = $this->postJson('/api/v1/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response
                ->assertOk()
                ->assertJsonStructure(['message']);
            Event::assertDispatched(PasswordReset::class);
            Event::assertListening(PasswordReset::class, InvalidateUserTokens::class);

            return true;
        });
    }

    public function test_password_cant_be_reset_with_invalid_token()
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->postJson('/api/v1/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
            $response = $this->postJson('/api/v1/reset-password', [
                'token' => 'invalid-token',
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response
                ->assertUnprocessable()
                ->assertJsonValidationErrorFor('token');

            return true;
        });
    }
}
