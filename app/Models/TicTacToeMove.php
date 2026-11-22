<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicTacToeMove extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'user_id',
        'symbol',
        'position',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(TicTacToeGame::class, 'game_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}