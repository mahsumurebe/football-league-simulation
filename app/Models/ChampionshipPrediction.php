<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChampionshipPrediction extends Model
{
    use HasFactory;

    protected $fillable = ['team_id', 'week', 'probability'];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
