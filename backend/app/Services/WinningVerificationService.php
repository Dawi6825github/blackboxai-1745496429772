<?php

namespace App\Services;

class WinningVerificationService
{
    /**
     * Verify if a card has won based on the pattern and called numbers.
     *
     * @param array $cardNumbers
     * @param array $patternGrid
     * @param array $calledNumbers
     * @return bool
     */
    public function verifyWin(array $cardNumbers, array $patternGrid, array $calledNumbers): bool
    {
        // Convert card to a 5x5 grid format
        $cardGrid = $this->convertCardToGrid($cardNumbers);
        
        // Check each cell in the pattern
        for ($row = 0; $row < 5; $row++) {
            for ($col = 0; $col < 5; $col++) {
                // If this cell is part of the pattern
                if ($patternGrid[$row][$col]) {
                    // Get the number at this position on the card
                    $number = $cardGrid[$row][$col];
                    
                    // Free space is always considered matched
                    if ($number === 'free') {
                        continue;
                    }
                    
                    // If the number hasn't been called, pattern is not complete
                    if (!in_array($number, $calledNumbers)) {
                        return false;
                    }
                }
            }
        }
        
        // If we get here, all required numbers have been called
        return true;
    }
    
    /**
     * Convert card from column-based format to grid format.
     *
     * @param array $cardNumbers
     * @return array
     */
    private function convertCardToGrid(array $cardNumbers): array
    {
        $grid = [];
        $columns = ['B', 'I', 'N', 'G', 'O'];
        
        for ($row = 0; $row < 5; $row++) {
            $grid[$row] = [];
            for ($col = 0; $col < 5; $col++) {
                $grid[$row][$col] = $cardNumbers[$columns[$col]][$row];
            }
        }
        
        return $grid;
    }
}
