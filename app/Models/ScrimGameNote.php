<?php

namespace App\Models;

use App\Enums\TypeScrimGameNote;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScrimGameNote extends Model
{
    protected $fillable = ['type', 'note', 'scrim_game_id'];

    protected function casts(): array
    {
        return [
            'type' => TypeScrimGameNote::class,
        ];
    }

    public function scrimGame(): BelongsTo
    {
        return $this->belongsTo(ScrimGame::class);
    }
}
