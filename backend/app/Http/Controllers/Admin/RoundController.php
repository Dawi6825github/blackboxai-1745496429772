<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoundRequest;
use App\Models\CalledNumber;
use App\Models\Round;
use Illuminate\Http\Request;

class RoundController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Round::with('patterns');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by active rounds
        if ($request->has('active') && $request->active) {
            $now = now();
            $query->where('status', true)
                ->where('start_time', '<=', $now)
                ->where('end_time', '>=', $now);
        }

        $rounds = $query->orderBy('start_time', 'desc')->paginate(10);

        return response()->json($rounds);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Admin\RoundRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(RoundRequest $request)
    {
        $round = Round::create([
            'name' => $request->name,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => $request->status,
            'min_bet' => $request->min_bet,
            'max_bet' => $request->max_bet,
            'commission_rate' => $request->commission_rate,
        ]);

        // Attach patterns
        if ($request->has('pattern_ids')) {
            $round->patterns()->attach($request->pattern_ids);
        }

        return response()->json($round->load('patterns'), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $round = Round::with(['patterns', 'calledNumbers'])->findOrFail($id);
        
        return response()->json($round);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Admin\RoundRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(RoundRequest $request, $id)
    {
        $round = Round::findOrFail($id);
        
        $round->update([
            'name' => $request->name,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => $request->status,
            'min_bet' => $request->min_bet,
            'max_bet' => $request->max_bet,
            'commission_rate' => $request->commission_rate,
        ]);

        // Sync patterns
        if ($request->has('pattern_ids')) {
            $round->patterns()->sync($request->pattern_ids);
        }

        return response()->json($round->load('patterns'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $round = Round::findOrFail($id);
        
        // Check if round has bets
        if ($round->bets()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete round as it has bets placed on it',
            ], 422);
        }
        
        $round->patterns()->detach();
        $round->calledNumbers()->delete();
        $round->delete();

        return response()->json(['message' => 'Round deleted successfully']);
    }

    /**
     * Call a number for the round.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function callNumber(Request $request, $id)
    {
        $request->validate([
            'number' => 'required|integer|min:1|max:75',
        ]);

        $round = Round::findOrFail($id);
        
        // Check if round is active
        if (!$round->isActive()) {
            return response()->json([
                'message' => 'Cannot call number for inactive round',
            ], 422);
        }
        
        // Check if number has already been called
        if ($round->isNumberCalled($request->number)) {
            return response()->json([
                'message' => 'Number has already been called',
            ], 422);
        }
        
        // Call the number
        $calledNumber = CalledNumber::create([
            'round_id' => $round->id,
            'number' => $request->number,
            'called_at' => now(),
        ]);

        return response()->json($calledNumber);
    }

    /**
     * Get called numbers for the round.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCalledNumbers($id)
    {
        $round = Round::findOrFail($id);
        $calledNumbers = $round->calledNumbers()->orderBy('called_at', 'desc')->get();
        
        return response()->json($calledNumbers);
    }
}
