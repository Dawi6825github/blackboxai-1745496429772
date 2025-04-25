<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bet extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'round_id',
        'pattern_id',
        'amount',
        'status',
        'winnings',
        'placed_at',
        'won_at',
        'lost_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'winnings' => 'decimal:2',
        'placed_at' => 'datetime',
        'won_at' => 'datetime',
        'lost_at' => 'datetime',
    ];

    /**
     * Get the user that owns the bet.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the round that the bet is for.
     */
    public function round()
    {
        return $this->belongsTo(Round::class);
    }

    /**
     * Get the pattern that the bet is for.
     */
    public function pattern()
    {
        return $this->belongsTo(Pattern::class);
    }

    /**
     * Get the cards for the bet.
     */
    public function cards()
    {
        return $this->belongsToMany(Card::class);
    }
}
