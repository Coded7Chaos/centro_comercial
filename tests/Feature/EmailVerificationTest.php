<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Notifications\VerifyEmail;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Guest cannot access email verification notice.
     */
    public function test_guest_cannot_access_verification_notice(): void
    {
        $response = $this->get(route('verification.notice'));
        $response->assertRedirect('/login');
    }

    /**
     * Authenticated unverified user can access email verification notice.
     */
    public function test_unverified_user_can_access_verification_notice(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get(route('verification.notice'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.verify-email');
    }

    /**
     * Test email verification with a valid signed URL.
     */
    public function test_email_can_be_verified(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect('/dashboard-cliente');
        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    /**
     * Test email verification fails with an invalid verification signature.
     */
    public function test_email_cannot_be_verified_with_invalid_signature(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // Modify the signature to make it invalid
        $badVerificationUrl = $verificationUrl . 'modified';

        $response = $this->actingAs($user)->get($badVerificationUrl);

        $response->assertStatus(403);
        $this->assertNull($user->refresh()->email_verified_at);
    }

    /**
     * Test resending email verification notification.
     */
    public function test_resend_verification_notification(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->post(route('verification.send'));

        $response->assertRedirect();
        $response->assertSessionHas('message', 'Link de verificación enviado.');

        Notification::assertSentTo($user, VerifyEmail::class);
    }
}
