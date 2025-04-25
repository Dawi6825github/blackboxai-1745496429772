<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Round extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'status',
        'min_bet',
        'max_bet',
        'commission_rate',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'status' => 'boolean',
        'min_bet' => 'decimal:2',
        'max_bet' => 'decimal:2',
        'commission_rate' => 'decimal:2',
    ];

    /**
     * Check if the round is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        $now = now();
        return $this->status && $this->start_time <= $now && $this->end_time >= $now;
    }

    /**
     * Check if a number has been called for this round.
     *
     * @param int $number
     * @return bool
     */
    public function isNumberCalled(int $number): bool
    {
        return $this->calledNumbers()->where('number', $number)->exists();
    }

    /**
     * Get the patterns for the round.
     */
    public function patterns()
    {
        return $this->belongsToMany(Pattern::class);
    }

    /**
     * Get the called numbers for the round.
     */
    public function calledNumbers()
    {
        return $this->hasMany(CalledNumber::class);
    }

    /**
     * Get the bets for the round.
     */
    public function bets()
    {
        return $this->hasMany(Bet::class);
    }
}
