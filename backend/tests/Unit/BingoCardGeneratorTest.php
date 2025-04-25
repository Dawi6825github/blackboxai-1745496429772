<?php

namespace App\Services;

class BingoCardGenerator
{
    /**
     * Generate a random bingo card
     *
     * @return array
     */
    public function generate(): array
    {
        $card = [];
        
        // Generate B column (1-15)
        $bColumn = $this->generateUniqueNumbers(1, 15, 5);
        
        // Generate I column (16-30)
        $iColumn = $this->generateUniqueNumbers(16, 30, 5);
        
        // Generate N column (31-45) - only 4 numbers as the middle is FREE
        $nColumn = $this->generateUniqueNumbers(31, 45, 4);
        
        // Generate G column (46-60)
        $gColumn = $this->generateUniqueNumbers(46, 60, 5);
        
        // Generate O column (61-75)
        $oColumn = $this->generateUniqueNumbers(61, 75, 5);
        
        // Create the card grid
        for ($row = 0; $row < 5; $row++) {
            $card[$row] = [];
            
            // B column
            $card[$row][0] = $bColumn[$row];
            
            // I column
            $card[$row][1] = $iColumn[$row];
            
            // N column
            if ($row == 2) {
                $card[$row][2] = 'FREE';
            } else {
                $nIndex = ($row > 2) ? $row - 1 : $row;
                $card[$row][2] = $nColumn[$nIndex];
            }
            
            // G column
            $card[$row][3] = $gColumn[$row];
            
            // O column
            $card[$row][4] = $oColumn[$row];
        }
        
        return $card;
    }
    
    /**
     * Generate an array of unique random numbers within a range
     *
     * @param int $min
     * @param int $max
     * @param int $count
     * @return array
     */
    private function generateUniqueNumbers(int $min, int $max, int $count): array
    {
        $numbers = range($min, $max);
        shuffle($numbers);
        
        return array_slice($numbers, 0, $count);
    }
    
    /**
     * Convert a card to a formatted string representation
     *
     * @param array $card
     * @return string
     */
    public function formatCard(array $card): string
    {
        $output = "B  I  N  G  O\n";
        $output .= "---------------\n";
        
        for ($row = 0; $row < 5; $row++) {
            for ($col = 0; $col < 5; $col++) {
                $value = $card[$row][$col];
                $output .= str_pad($value, 2, ' ', STR_PAD_LEFT) . ' ';
            }
            $output .= "\n";
        }
        
        return $output;
    }
    
    /**
     * Convert a number to its bingo call format (e.g., 1 -> B1, 16 -> I16)
     *
     * @param int $number
     * @return string
     */
    public static function numberToCall(int $number): string
    {
        if ($number >= 1 && $number <= 15) {
            return "B{$number}";
        } elseif ($number >= 16 && $number <= 30) {
            return "I{$number}";
        } elseif ($number >= 31 && $number <= 45) {
            return "N{$number}";
        } elseif ($number >= 46 && $number <= 60) {
            return "G{$number}";
        } elseif ($number >= 61 && $number <= 75) {
            return "O{$number}";
        }
        
        return (string) $number;
    }
    
    /**
     * Convert a bingo call to its number (e.g., B1 -> 1, I16 -> 16)
     *
     * @param string $call
     * @return int|null
     */
    public static function callToNumber(string $call): ?int
    {
        if (preg_match('/^([BINGO])(\d+)$/', $call, $matches)) {
            return (int) $matches[2];
        }
        
        return null;
    }
    
    /**
     * Get the letter for a number (e.g., 1 -> B, 16 -> I)
     *
     * @param int $number
     * @return string
     */
    public static function getLetterForNumber(int $number): string
    {
        if ($number >= 1 && $number <= 15) {
            return "B";
        } elseif ($number >= 16 && $number <= 30) {
            return "I";
        } elseif ($number >= 31 && $number <= 45) {
            return "N";
        } elseif ($number >= 46 && $number <= 60) {
            return "G";
        } elseif ($number >= 61 && $number <= 75) {
            return "O";
        }
        
        return "";
    }
}
