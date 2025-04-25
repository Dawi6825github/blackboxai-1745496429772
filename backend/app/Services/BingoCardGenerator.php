<?php

namespace App\Services;

class BingoCardGenerator
{
    /**
     * Generate a random bingo card.
     *
     * @return array
     */
    public function generate(): array
    {
        $card = [];
        
        // B column (1-15)
        $card['B'] = $this->getRandomNumbers(1, 15, 5);
        
        // I column (16-30)
        $card['I'] = $this->getRandomNumbers(16, 30, 5);
        
        // N column (31-45) with center free space
        $nColumn = $this->getRandomNumbers(31, 45, 4);
        array_splice($nColumn, 2, 0, ['free']);
        $card['N'] = $nColumn;
        
        // G column (46-60)
        $card['G'] = $this->getRandomNumbers(46, 60, 5);
        
        // O column (61-75)
        $card['O'] = $this->getRandomNumbers(61, 75, 5);
        
        return $card;
    }
    
    /**
     * Get an array of random unique numbers within a range.
     *
     * @param int $min
     * @param int $max
     * @param int $count
     * @return array
     */
    private function getRandomNumbers(int $min, int $max, int $count): array
    {
        $numbers = range($min, $max);
        shuffle($numbers);
        return array_slice($numbers, 0, $count);
    }
}
