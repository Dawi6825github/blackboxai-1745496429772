<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pattern extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'grid',
    ];

    /**
     * Get the rounds for the pattern.
     */
    public function rounds()
    {
        return $this->belongsToMany(Round::class);
    }

    /**
     * Get the bets for the pattern.
     */
    public function bets()
    {
        return $this->hasMany(Bet::class);
    }
}
