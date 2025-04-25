<?php

namespace Tests\Unit;

use App\Models\Bet;
use App\Models\CalledNumbers;
use App\Models\Card;
use App\Models\Pattern;
use App\Models\Round;
use App\Services\WinningVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WinningVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected $service;
    protected $round;
    protected $pattern;
    protected $card;
    protected $bet;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = new WinningVerificationService();
        
        // Create a pattern (horizontal line on top row)
        $this->pattern = Pattern::factory()->create([
            'positions' => json_encode([[0, 0], [0, 1], [0, 2], [0, 3], [0, 4]])
        ]);
        
        // Create a round with the pattern
        $this->round = Round::factory()->create(['status' => 'active']);
        $this->round->patterns()->attach($this->pattern->id);
        
        // Create a card with known values for the top row
        $cardData = [
            [1, 16, 31, 46, 61], // Top row - these are the numbers we'll test
            [2, 17, 32, 47, 62],
            [3, 18, 'FREE', 48, 63],
            [4, 19, 33, 49, 64],
            [5, 20, 34, 50, 65]
        ];
        
        $this->card = Card::factory()->create([
            'data' => json_encode($cardData)
        ]);
        
        // Create a bet
        $this->bet = Bet::factory()->create([
            'round_id' => $this->round->id,
            'status' => 'active'
        ]);
        
        $this->bet->cards()->attach($this->card->id);
    }

    public function test_verifies_winning_pattern()
    {
        // Call all numbers in the top row
        CalledNumbers::create(['round_id' => $this->round->id, 'number' => 'B1']);
        CalledNumbers::create(['round_id' => $this->round->id, 'number' => 'I16']);
        CalledNumbers::create(['round_id' => $this->round->id, 'number' => 'N31']);
        CalledNumbers::create(['round_id' => $this->round->id, 'number' => 'G46']);
        CalledNumbers::create(['round_id' => $this->round->id, 'number' => 'O61']);
        
        // Verify the win
        $result = $this->service->verifyWin($this->bet, $this->card);
        
        $this->assertTrue($result);
    }

    public function test_rejects_incomplete_pattern()
    {
        // Call only some numbers in the top row
        CalledNumbers::create(['round_id' => $this->round->id, 'number' => 'B1']);
        CalledNumbers::create(['round_id' => $this->round->id, 'number' => 'I16']);
        CalledNumbers::create(['round_id' => $this->round->id, 'number' => 'N31']);
        // Missing G46 and O61
        
        // Verify the win
        $result = $this->service->verifyWin($this->bet, $this->card);
        
        $this->assertFalse($result);
    }

    public function test_verifies_pattern_with_free_space()
    {
        // Create a middle row pattern (includes FREE space)
        $middleRowPattern = Pattern::factory()->create([
            'positions' => json_encode([[2, 0], [2, 1], [2, 2], [2, 3], [2, 4]])
        ]);
        
        $this->round->patterns()->attach($middleRowPattern->id);
        
        // Call all numbers in the middle row except the FREE space
        CalledNumbers::create(['round_id' => $this->round->id, 'number' => 'B3']);
        CalledNumbers::create(['round_id' => $this->round->id, 'number' => 'I18']);
        // FREE space in the middle
        CalledNumbers::create(['round_id' => $this->round->id, 'number' => 'G48']);
        CalledNumbers::create(['round_id' => $this->round->id, 'number' => 'O63']);
        
        // Verify the win
        $result = $this->service->verifyWin($this->bet, $this->card);
        
        $this->assertTrue($result);
    }

    public function test_handles_multiple_patterns()
    {
        // Create a diagonal pattern
        $diagonalPattern = Pattern::factory()->create([
            'positions' => json_encode([[0, 0], [1, 1], [2, 2], [3, 3], [4, 4]])
        ]);
        
        $this->round->patterns()->attach($diagonalPattern->id);
        
        // Call all numbers in the diagonal except the FREE space
        CalledNumbers::create(['round_id' => $this->round->id, 'number' => 'B1']);
        CalledNumbers::create(['round_id' => $this->round->id, 'number' => 'I17']);
        // FREE space in the middle
        CalledNumbers::create(['round_id' => $this->round->id, 'number' => 'G49']);
        CalledNumbers::create(['round_id' => $this->round->id, 'number' => 'O65']);
        
        // Verify the win
        $result = $this->service->verifyWin($this->bet, $this->card);
        
        $this->assertTrue($result);
    }

    public function test_rejects_win_for_inactive_round()
    {
        // Call all numbers in the top row
        CalledNumbers::create(['round_id' => $this->round->id, 'number' => 'B1']);
        CalledNumbers::create(['round_id' => $this->round->id, 'number' => 'I16']);
        CalledNumbers::create(['round_id' => $this->round->id, 'number' => 'N31']);
        CalledNumbers::create(['round_id' => $this->round->id, 'number' => 'G46']);
        CalledNumbers::create(['round_id' => $this->round->id, 'number' => 'O61']);
        
        // Set round to completed
        $this->round->update(['status' => 'completed']);
        
        // Verify the win
        $result = $this->service->verifyWin($this->bet, $this->card);
        
        $this->assertFalse($result);
    }
}
