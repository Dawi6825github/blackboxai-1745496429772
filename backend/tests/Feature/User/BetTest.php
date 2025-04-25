<?php

namespace Tests\Feature\User;

use App\Models\Bet;
use App\Models\Card;
use App\Models\Round;
use App\Models\User;
use App\Services\WinningVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Mockery;

class BetTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $round;
    protected $card;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'balance' => 100
        ]);
        
        $this->round = Round::factory()->create([
            'status' => 'active',
            'entry_fee' => 10
        ]);
        
        $this->card = Card::factory()->create([
            'user_id' => $this->user->id
        ]);
    }

    public function test_user_can_view_bets()
    {
        $bets = Bet::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'round_id' => $this->round->id
        ]);
        
        $response = $this->actingAs($this->user)
                         ->get(route('user.bets.index'));
        
        $response->assertStatus(200);
        $response->assertViewHas('bets');
        
        foreach ($bets as $bet) {
            $response->assertSee($bet->round->name);
        }
    }

    public function test_user_can_place_bet()
    {
        $betData = [
            'round_id' => $this->round->id,
            'amount' => 10,
            'cards' => [$this->card->id]
        ];
        
        $response = $this->actingAs($this->user)
                         ->post(route('user.bets.store'), $betData);
        
        $response->assertRedirect(route('user.bets.index'));
        
        $this->assertDatabaseHas('bets', [
            'user_id' => $this->user->id,
            'round_id' => $this->round->id,
            'amount' => 10,
            'status' => 'active'
        ]);
        
        // Check if the user's balance was deducted
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'balance' => 90
        ]);
        
        // Check if the card is attached to the bet
        $bet = Bet::where('user_id', $this->user->id)
                  ->where('round_id', $this->round->id)
                  ->first();
                  
        $this->assertTrue($bet->cards->contains($this->card->id));
    }

    public function test_user_cannot_place_bet_with_insufficient_balance()
    {
        // Update user balance to be less than the entry fee
        $this->user->update(['balance' => 5]);
        
        $betData = [
            'round_id' => $this->round->id,
            'amount' => 10,
            'cards' => [$this->card->id]
        ];
        
        $response = $this->actingAs($this->user)
                         ->post(route('user.bets.store'), $betData);
        
        $response->assertSessionHasErrors('amount');
        
        $this->assertDatabaseMissing('bets', [
            'user_id' => $this->user->id,
            'round_id' => $this->round->id
        ]);
    }

    public function test_user_can_claim_win()
    {
        $bet = Bet::factory()->create([
            'user_id' => $this->user->id,
            'round_id' => $this->round->id,
            'amount' => 10,
            'status' => 'active'
        ]);
        
        $bet->cards()->attach($this->card->id);
        
        // Mock the winning verification service to return true
        $mock = Mockery::mock(WinningVerificationService::class);
        $mock->shouldReceive('verifyWin')->once()->andReturn(true);
        $this->app->instance(WinningVerificationService::class, $mock);
        
        $response = $this->actingAs($this->user)
                         ->post(route('user.bets.claim-win', $bet->id));
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('bets', [
            'id' => $bet->id,
            'status' => 'won',
            'is_winner' => true
        ]);
    }

    public function test_user_cannot_claim_invalid_win()
    {
        $bet = Bet::factory()->create([
            'user_id' => $this->user->id,
            'round_id' => $this->round->id,
            'amount' => 10,
            'status' => 'active'
        ]);
        
        $bet->cards()->attach($this->card->id);
        
        // Mock the winning verification service to return false
        $mock = Mockery::mock(WinningVerificationService::class);
        $mock->shouldReceive('verifyWin')->once()->andReturn(false);
        $this->app->instance(WinningVerificationService::class, $mock);
        
        $response = $this->actingAs($this->user)
                         ->post(route('user.bets.claim-win', $bet->id));
        
        $response->assertRedirect();
        $response->assertSessionHas('error');
        
        $this->assertDatabaseHas('bets', [
            'id' => $bet->id,
            'status' => 'active',
            'is_winner' => false
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
