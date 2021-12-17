<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VerifyEmailControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_notification_can_be_sended()
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/v1/verify-email/send');

        $response
            ->assertOk()
            ->assertJson(['status' => true]);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_notification_cant_be_sended_if_already_verified()
    {
        Notification::fake();
        $user = User::factory()->create();

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/v1/verify-email/send');

        $response
            ->assertOk()
            ->assertJson(['status' => false]);
        Notification::assertNothingSent();
    }

    public function test_email_can_be_verified()
    {
        Event::fake();
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(RouteServiceProvider::HOME.'?verified=1');
    }

    public function test_email_is_not_verified_with_invalid_hash()
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $response = $this->get($verificationUrl);

        $response->assertForbidden();
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }
}
