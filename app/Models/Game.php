<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'home_team_id',
        'away_team_id',
        'home_score',
        'away_score',
        'week',
        'played',
    ];

    protected $casts = [
        'played' => 'boolean',
    ];

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function scopePlayed($query)
    {
        return $query->where('played', true);
    }

    public function scopeNotPlayed($query)
    {
        return $query->where('played', false);
    }
}
