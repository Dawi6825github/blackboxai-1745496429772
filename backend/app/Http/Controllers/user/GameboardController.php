<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\BetRequest;
use App\Models\Bet;
use App\Models\Card;
use App\Models\Round;
use App\Services\BingoCardGenerator;
use App\Services\WinningVerificationService;
use Illuminate\Http\Request;

class GameboardController extends Controller
{
    /**
     * Get the current game state.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGameState(Request $request)
    {
        $user = $request->user();
        
        // Get active rounds
        $now = now();
        $activeRounds = Round::with('patterns')
            ->where('status', true)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->get();
        
        // Get user's active bets
        $activeBets = Bet::with(['round', 'pattern', 'cards'])
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->get();
        
        // Get called numbers for active rounds
        $calledNumbers = [];
        foreach ($activeRounds as $round) {
            $calledNumbers[$round->id] = $round->calledNumbers()
                ->orderBy('called_at', 'desc')
                ->get()
                ->pluck('number')
                ->toArray();
        }
        
        return response()->json([
            'activeRounds' => $activeRounds,
            'activeBets' => $activeBets,
            'calledNumbers' => $calledNumbers,
        ]);
    }

    /**
     * Place a bet.
     *
     * @param  \App\Http\Requests\User\BetRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function placeBet(BetRequest $request)
    {
        $user = $request->user();
        $round = Round::findOrFail($request->round_id);
        
        // Check if round is active
        if (!$round->isActive()) {
            return response()->json([
                'message' => 'Cannot place bet on inactive round',
            ], 422);
        }
        
        // Check if bet amount is within limits
        if ($request->amount < $round->min_bet || $request->amount > $round->max_bet) {
            return response()->json([
                'message' => "Bet amount must be between {$round->min_bet} and {$round->max_bet}",
            ], 422);
        }
        
        // Check if cards belong to user
        $cards = Card::where('user_id', $user->id)
            ->whereIn('id', $request->card_ids)
            ->get();
        
        if ($cards->count() !== count($request->card_ids)) {
            return response()->json([
                'message' => 'One or more cards do not belong to you',
            ], 422);
        }
        
        // Create bet
        $bet = Bet::create([
            'user_id' => $user->id,
            'round_id' => $round->id,
            'pattern_id' => $request->pattern_id,
            'amount' => $request->amount,
            'status' => 'active',
            'placed_at' => now(),
        ]);
        
        // Attach cards to bet
        $bet->cards()->attach($request->card_ids);
        
        return response()->json($bet->load(['round', 'pattern', 'cards']), 201);
    }

    /**
     * Get random cards for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Services\BingoCardGenerator  $cardGenerator
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRandomCards(Request $request, BingoCardGenerator $cardGenerator)
    {
        $request->validate([
            'count' => 'required|integer|min:1|max:10',
        ]);
        
        $user = $request->user();
        $cards = [];
        
        for ($i = 0; $i < $request->count; $i++) {
            $numbers = $cardGenerator->generate();
            
            $card = Card::create([
                'user_id' => $user->id,
                'numbers' => json_encode($numbers),
                'status' => true,
            ]);
            
            $cards[] = $card;
        }
        
        return response()->json($cards, 201);
    }

    /**
     * Check for winning cards.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Services\WinningVerificationService  $winningService
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkWinningCards(Request $request, WinningVerificationService $winningService)
    {
        $request->validate([
            'round_id' => 'required|exists:rounds,id',
            'bet_id' => 'required|exists:bets,id',
        ]);
        
        $user = $request->user();
        $round = Round::findOrFail($request->round_id);
        $bet = Bet::where('id', $request->bet_id)
            ->where('user_id', $user->id)
            ->where('round_id', $round->id)
            ->where('status', 'active')
            ->firstOrFail();
        
        // Get called numbers
        $calledNumbers = $round->calledNumbers()->pluck('number')->toArray();
        
        // Get pattern
        $pattern = $bet->pattern;
        $patternGrid = json_decode($pattern->grid, true);
        
        // Check each card for a win
        $winningCards = [];
        foreach ($bet->cards as $card) {
            $cardNumbers = json_decode($card->numbers, true);
            
            if ($winningService->verifyWin($cardNumbers, $patternGrid, $calledNumbers)) {
                $winningCards[] = $card->id;
            }
        }
        
        // If there are winning cards, update the bet
        if (count($winningCards) > 0) {
            // Calculate winnings (simple implementation)
            $winnings = $bet->amount * 10; // Example: 10x the bet amount
            
            $bet->update([
                'status' => 'won',
                'winnings' => $winnings,
                'won_at' => now(),
            ]);
            
            return response()->json([
                'status' => 'won',
                'winning_cards' => $winningCards,
                'winnings' => $winnings,
            ]);
        }
        
        return response()->json([
            'status' => 'no_win',
            'message' => 'No winning cards found',
        ]);
    }
}
