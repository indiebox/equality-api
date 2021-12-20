<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SendEmailVerificationLinkControllerTest extends TestCase
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
}
