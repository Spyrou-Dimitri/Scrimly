<?php

namespace App\Models;

use App\Enums\LolTier;
use Database\Factories\RiotProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiotProfile extends Model
{
    /** @use HasFactory<RiotProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'riot_tag',
        'riot_puuid',
        'tier',
        'rank',
        'lp',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tier' => LolTier::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
