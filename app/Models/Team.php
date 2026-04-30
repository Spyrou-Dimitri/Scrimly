<?php

namespace App\Models;

use App\Enums\Language;
use App\Enums\LolGoal;
use App\Enums\LolServeur;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'tag',
        'code',
        'logo',
        'description',
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
            ->withPivot('roleInTeam', 'roleInGame', 'joined_at')
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
}
