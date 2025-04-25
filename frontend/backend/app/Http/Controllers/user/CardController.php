<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\CardRequest;
use App\Models\Card;
use App\Services\BingoCardGenerator;
use Illuminate\Http\Request;

class CardController extends Controller
{
    /**
     * Display a listing of the user's cards.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $cards = $user->cards()->where('status', true)->paginate(10);
        
        return response()->json($cards);
    }

    /**
     * Store a newly created card in storage.
     *
     * @param  \App\Http\Requests\User\CardRequest  $request
     * @param  \App\Services\BingoCardGenerator  $cardGenerator
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CardRequest $request, BingoCardGenerator $cardGenerator)
    {
        $user = $request->user();
        
        // Generate a new card
        $numbers = $cardGenerator->generate();
        
        $card = Card::create([
            'user_id' => $user->id,
            'numbers' => json_encode($numbers),
            'status' => true,
        ]);
        
        return response()->json($card, 201);
    }

    /**
     * Display the specified card.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $card = Card::where('user_id', $user->id)->findOrFail($id);
        $card->numbers_array = json_decode($card->numbers);
        
        return response()->json($card);
    }

    /**
     * Update the specified card status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|boolean',
        ]);
        
        $user = $request->user();
        $card = Card::where('user_id', $user->id)->findOrFail($id);
        
        $card->update([
            'status' => $request->status,
        ]);
        
        return response()->json($card);
    }

    /**
     * Generate printable cards.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPrintableCards(Request $request)
    {
        $request->validate([
            'card_ids' => 'required|array',
            'card_ids.*' => 'exists:cards,id',
        ]);
        
        $user = $request->user();
        $cards = Card::where('user_id', $user->id)
            ->whereIn('id', $request->card_ids)
            ->get();
        
        // Transform cards for printing
        $printableCards = $cards->map(function ($card) {
            return [
                'id' => $card->id,
                'numbers' => json_decode($card->numbers),
                'created_at' => $card->created_at->format('Y-m-d H:i:s'),
            ];
        });
        
        return response()->json($printableCards);
    }
}
