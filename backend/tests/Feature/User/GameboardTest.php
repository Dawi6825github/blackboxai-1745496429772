<?php

namespace Tests\Feature\User;

use App\Models\Bet;
use App\Models\CalledNumbers;
use App\Models\Card;
use App\Models\Pattern;
use App\Models\Round;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GameboardTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $round;
    protected $pattern;
    protected $card;
    protected $bet;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->pattern = Pattern::factory()->create();
        
        $this->round = Round::factory()->create([
            'status' => 'active'
        ]);
        $this->round->patterns()->attach($this->pattern->id);
        
        $this->card = Card::factory()->create([
            'user_id' => $this->user->id
        ]);
        
        $this->bet = Bet::factory()->create([
            'user_id' => $this->user->id,
            'round_id' => $this->round->id,
            'status' => 'active'
        ]);
        $this->bet->cards()->attach($this->card->id);
    }

    public function test_user_can_view_gameboard()
    {
        $response = $this->actingAs($this->user)
                         ->get(route('user.gameboard'));
        
        $response->assertStatus(200);
        $response->assertViewHas('activeRounds');
        $response->assertSee($this->round->name);
    }

    public function test_user_can_view_specific_round_gameboard()
    {
        // Create some called numbers for the round
        CalledNumbers::create([
            'round_id' => $this->round->id,
            'number' => 'B5'
        ]);
        
        CalledNumbers::create([
            'round_id' => $this->round->id,
            'number' => 'I16'
        ]);
        
        $response = $this->actingAs($this->user)
                         ->get(route('user.gameboard.show', $this->round->id));
        
        $response->assertStatus(200);
        $response->assertViewHas('round');
        $response->assertViewHas('cards');
        $response->assertViewHas('calledNumbers');
        $response->assertSee($this->round->name);
        $response->assertSee('B5');
        $response->assertSee('I16');
    }

    public function test_user_cannot_view_inactive_round_gameboard()
    {
        // Update round to be inactive
        $this->round->update(['status' => 'completed']);
        
        $response = $this->actingAs($this->user)
                         ->get(route('user.gameboard.show', $this->round->id));
        
        $response->assertRedirect(route('user.gameboard'));
        $response->assertSessionHas('error');
    }

    public function test_user_cannot_view_round_without_bet()
    {
        // Delete the bet
        $this->bet->delete();
        
        $response = $this->actingAs($this->user)
                         ->get(route('user.gameboard.show', $this->round->id));
        
        $response->assertRedirect(route('user.gameboard'));
        $response->assertSessionHas('error');
    }
}
