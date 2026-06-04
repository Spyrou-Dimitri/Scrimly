<?php

namespace App\Models;

use App\Enums\Language;
use App\Enums\LolGoal;
use App\Enums\LolServeur;
use App\Enums\ScrimOutcome;
use App\Enums\StatusInTeam;
use App\Enums\StatusScrim;
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

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function scrims(): HasMany
    {
        return $this->hasMany(Scrim::class);
    }

    public function sentScrimRequests(): HasMany
    {
        return $this->hasMany(ScrimRequest::class, 'requester_team_id');
    }

    public function receivedScrimRequests(): HasMany
    {
        return $this->hasMany(ScrimRequest::class, 'receiver_team_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function teamInvitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class);
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
            return Storage::disk(config('logoTeam.disk'))->url('images/logoTeam/variants/300x300/'.$this->logo_value);
        }

        if ($this->logo_type === 'default' && $this->logo_value) {
            return asset('img/IconsTeams/'.$this->logo_value.'.webp');
        }

        return asset('img/IconsTeams/Demacia.webp');
    }

    public function getLogoSrcsetAttribute(): ?string
    {
        if ($this->logo_type !== 'upload' || blank($this->logo_value)) {
            return null;
        }
        $value = $this->logo_value;
        $url = fn ($size) => Storage::disk(config('logoTeam.disk'))->url('images/logoTeam/variants/'.$size.'x'.$size.'/'.$value);

        return "{$url(80)} 80w, {$url(300)} 300w, {$url(400)} 400w";

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
    public function reloadCode(): void
    {
        $this->code = self::uniqueCodeGenerator();
        $this->save();
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

        if ($averageWithoutNull === []) {
            return null;
        }

        $averageEloOfTeam = array_sum($averageWithoutNull) / count($averageWithoutNull);

        $this->starter_average_elo = round($averageEloOfTeam);
        $this->save();
    }

    public function overallScrimWinrate(): float
    {
        $query = Scrim::query()
            ->where('team_id', $this->id)
            ->whereIn('status', [StatusScrim::COMPLETED, StatusScrim::ABORTED]);
        $allScrim = $query->count();
        if ($allScrim === 0) {
            return 0.0;
        }
        $allWinScrim = $query
            ->whereIn('status', [StatusScrim::COMPLETED, StatusScrim::ABORTED])
            ->where('outcome', ScrimOutcome::Victory)
            ->count();

        return round(($allWinScrim / $allScrim) * 100, 2);
    }
}
