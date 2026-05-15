<?php

namespace App\Models;

use App\Enums\Language;
use App\Enums\LolGoal;
use App\Enums\LolServeur;
use App\Enums\StatusInTeam;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'tag',
        'code',
        'logo_type',
        'logo_value',
        'description',
        'starter_average_elo',
        'language',
        'server',
        'goal',
        'creator_id',
    ];

    protected $casts = [
        'language' => Language::class,
        'server' => LolServeur::class,
        'goal' => LolGoal::class,
    ];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_members')
            ->withPivot('roleInTeam', 'roleInGame', 'joined_at', 'is_starter', 'status')
            ->withTimestamps();
    }

    public function teamApplications(): HasMany
    {
        return $this->hasMany(TeamApplication::class);
    }

    protected function tag(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => strtoupper($value),
            set: fn ($value) => strtoupper($value),
        );
    }

    protected static function booted()
    {
        static::creating(function ($team) {
            $team->code = self::uniqueCodeGenerator();
        });
    }

    public function getLogoUrlAttribute(): string
    {
        if ($this->logo_type === 'upload' && $this->logo_value) {
            return Storage::disk('public')->url('images/logoTeam/variants/480x480/'.$this->logo_value);
        }

        if ($this->logo_type === 'default' && $this->logo_value) {
            return asset('img/IconsTeams/'.$this->logo_value.'.webp');
        }

        return asset('img/IconsTeams/Demacia/.webp');
    }

    private static function uniqueCodeGenerator(): string
    {
        $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $code = '';
        $length = 6;
        for ($i = 0; $i < $length; $i++) {
            $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
        if (self::where('code', $code)->exists()) {
            return self::uniqueCodeGenerator();
        }

        return $code;
    }

    public function averageEloScore()
    {
        $scores = [];
        $startersPlayer = $this->members()->with('riotProfile')->wherePivot('is_starter', true)->wherePivot('status', StatusInTeam::ACCEPTED)->get();
        foreach ($startersPlayer as $player) {
            $scores[] = $player->riotProfile?->eloScore();
        }

        if ($scores === []) {
            return null;
        }
        $averageWithoutNull = array_filter($scores);

        $averageEloOfTeam = array_sum($averageWithoutNull) / count($averageWithoutNull);

        $this->starter_average_elo = round($averageEloOfTeam);
        $this->save();
    }
}
