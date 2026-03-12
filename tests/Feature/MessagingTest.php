<?php

namespace Tests\Feature;

use App\Models\Connection;
use App\Models\User;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessagingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that connected users can exchange messages.
     */
    public function test_connected_users_can_send_messages(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Establish connection
        Connection::create([
            'user_id' => $userA->id,
            'connected_user_id' => $userB->id,
            'status' => 'accepted'
        ]);

        $this->actingAs($userA);

        $response = $this->post(route('messaging.store', $userB), [
            'content' => 'Hello from User A'
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('messages', [
            'sender_id' => $userA->id,
            'receiver_id' => $userB->id,
            'content' => 'Hello from User A'
        ]);
    }

    /**
     * Test that non-connected users cannot message each other.
     */
    public function test_non_connected_users_cannot_send_messages(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $this->actingAs($userA);

        $response = $this->post(route('messaging.store', $userB), [
            'content' => 'Should fail'
        ]);

        $response->assertSessionHas('error', 'You can only message your connections.');
        $this->assertDatabaseCount('messages', 0);
    }
}
