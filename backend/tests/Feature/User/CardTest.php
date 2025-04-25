<?php

namespace Tests\Feature\User;

use App\Models\Card;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CardTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'balance' => 100
        ]);
    }

    public function test_user_can_view_cards()
    {
        $cards = Card::factory()->count(3)->create([
            'user_id' => $this->user->id
        ]);
        
        $response = $this->actingAs($this->user)
                         ->get(route('user.cards.index'));
        
        $response->assertStatus(200);
        $response->assertViewHas('cards');
        
        foreach ($cards as $card) {
            $response->assertSee('Card #' . $card->id);
        }
    }

    public function test_user_can_purchase_card()
    {
        $cardData = [
            'quantity' => 2,
            'price_per_card' => 5
        ];
        
        $response = $this->actingAs($this->user)
                         ->post(route('user.cards.store'), $cardData);
        
        $response->assertRedirect(route('user.cards.index'));
        
        // Check if cards were created
        $this->assertEquals(2, Card::where('user_id', $this->user->id)->count());
        
        // Check if user balance was deducted
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'balance' => 90 // 100 - (2 * 5)
        ]);
    }

    public function test_user_cannot_purchase_card_with_insufficient_balance()
    {
        // Update user balance to be less than required
        $this->user->update(['balance' => 5]);
        
        $cardData = [
            'quantity' => 2,
            'price_per_card' => 5
        ];
        
        $response = $this->actingAs($this->user)
                         ->post(route('user.cards.store'), $cardData);
        
        $response->assertSessionHasErrors('quantity');
        
        // Check that no cards were created
        $this->assertEquals(0, Card::where('user_id', $this->user->id)->count());
        
        // Check that user balance remains unchanged
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'balance' => 5
        ]);
    }

    public function test_user_can_view_card_details()
    {
        $card = Card::factory()->create([
            'user_id' => $this->user->id
        ]);
        
        $response = $this->actingAs($this->user)
                         ->get(route('user.cards.show', $card));
        
        $response->assertStatus(200);
        $response->assertViewHas('card');
        $response->assertSee('Card #' . $card->id);
    }

    public function test_user_cannot_view_other_users_cards()
    {
        $otherUser = User::factory()->create();
        $card = Card::factory()->create([
            'user_id' => $otherUser->id
        ]);
        
        $response = $this->actingAs($this->user)
                         ->get(route('user.cards.show', $card));
        
        $response->assertStatus(403);
    }
}
