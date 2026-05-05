<?php

namespace App\Models;

use App\Enums\LolTier;
use Database\Factories\RiotProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'wins',
        'losses',
        'synced_at',
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

    public function riotMatches(): HasMany
    {
        return $this->hasMany(RiotMatch::class);
    }
    public function getWinratePercentage()
    {
        $totalMatches = $this->wins + $this->losses;
        if ($totalMatches === 0) {
            return 0;
        }
        return round(($this->wins / $totalMatches) * 100);
    }
    public function getGeneralKda(): float
    {
        $allMatches = $this->riotMatches;
        if ($allMatches->count() === 0) {
            return 0;
        }
        $numberOfDeaths = $allMatches->sum('deaths');
        $numberOfKills = $allMatches->sum('kills');
        $numberOfAssists = $allMatches->sum('assists');
        $totalKda = $numberOfKills + $numberOfAssists;
        if ($numberOfDeaths === 0) {
            return 0;
        }
        return round($totalKda / $numberOfDeaths, 2);
    }
}
