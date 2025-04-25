<?php

namespace App\Services;

use App\Events\NumberCalled;
use App\Models\CalledNumbers;
use App\Models\Round;
use Illuminate\Support\Facades\Log;

class NumberCallerService
{
    /**
     * Call a random number for a round
     *
     * @param Round $round
     * @return string|null
     */
    public function callNumber(Round $round): ?string
    {
        // Check if round is active
        if ($round->status !== 'active') {
            Log::warning("Attempted to call number for inactive round: {$round->id}");
            return null;
        }
        
        // Get all possible bingo numbers
        $allNumbers = $this->getAllBingoNumbers();
        
        // Get already called numbers for this round
        $calledNumbers = CalledNumbers::where('round_id', $round->id)
            ->pluck('number')
            ->toArray();
        
        // Filter out already called numbers
        $availableNumbers = array_diff($allNumbers, $calledNumbers);
        
        // If all numbers have been called, return null
        if (empty($availableNumbers)) {
            Log::info("All numbers have been called for round: {$round->id}");
            return null;
        }
        
        // Select a random number
        $randomNumber = $availableNumbers[array_rand($availableNumbers)];
        
        // Record the called number
        CalledNumbers::create([
            'round_id' => $round->id,
            'number' => $randomNumber,
            'called_at' => now()
        ]);
        
        // Broadcast the called number
        event(new NumberCalled($round, $randomNumber));
        
        return $randomNumber;
    }
    
    /**
     * Get all possible bingo numbers in call format (B1, I16, etc.)
     *
     * @return array
     */
    private function getAllBingoNumbers(): array
    {
        $numbers = [];
        
        // B column (1-15)
        for ($i = 1; $i <= 15; $i++) {
            $numbers[] = "B{$i}";
        }
        
        // I column (16-30)
        for ($i = 16; $i <= 30; $i++) {
            $numbers[] = "I{$i}";
        }
        
        // N column (31-45)
        for ($i = 31; $i <= 45; $i++) {
            $numbers[] = "N{$i}";
        }
        
        // G column (46-60)
        for ($i = 46; $i <= 60; $i++) {
            $numbers[] = "G{$i}";
        }
        
        // O column (61-75)
        for ($i = 61; $i <= 75; $i++) {
            $numbers[] = "O{$i}";
        }
        
        return $numbers;
    }
    
    /**
     * Get all called numbers for a round
     *
     * @param Round $round
     * @return array
     */
    public function getCalledNumbers(Round $round): array
    {
        return CalledNumbers::where('round_id', $round->id)
            ->orderBy('called_at', 'asc')
            ->pluck('number')
            ->toArray();
    }
    
    /**
     * Check if a number has been called in a round
     *
     * @param Round $round
     * @param string $number
     * @return bool
     */
    public function isNumberCalled(Round $round, string $number): bool
    {
        return CalledNumbers::where('round_id', $round->id)
            ->where('number', $number)
            ->exists();
    }
}
