<?php

namespace Tests\Feature;

use App\Models\NewsletterSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_can_subscribe_to_the_newsletter(): void
    {
        $response = $this->from('/')->post('/newsletter', [
            'email' => 'cliente@example.com',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('newsletter_status');

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'cliente@example.com',
        ]);
    }

    public function test_the_email_must_be_valid(): void
    {
        $response = $this->from('/')->post('/newsletter', [
            'email' => 'no-es-un-email',
        ]);

        $response->assertSessionHasErrors('email');

        $this->assertDatabaseCount('newsletter_subscribers', 0);
    }
}
