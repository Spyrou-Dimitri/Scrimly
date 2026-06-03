<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\LolTier;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $fillable = [
        'username',
        'email',
        'password',
        'avatar_type',
        'avatar_value',
        'locale',
        'current_team_id',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar_type === 'upload' && $this->avatar_value) {
            return Storage::disk(config('avatar.disk'))->url('images/avatar/variants/400x400/'.$this->avatar_value);
        }

        if ($this->avatar_type === 'default' && $this->avatar_value) {
            return asset('img/IconsAvatars/'.$this->avatar_value.'.jpg');
        }

        return asset('img/avatars/defaults/Camille.webp');
    }

    public function getAvatarSrcsetAttribute(): ?string
    {
        if ($this->avatar_type !== 'upload' || blank($this->avatar_value)) {
            return null;
        }
        $value = $this->avatar_value;
        $url = fn (int $size) => Storage::disk(config('avatar.disk'))->url("images/avatar/variants/{$size}x{$size}/{$value}");

        return "{$url(70)} 70w, {$url(400)} 400w, {$url(620)} 620w";

    }

    protected function riotTag(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->riotProfile?->riot_tag);
    }

    protected function riotPuuid(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->riotProfile?->riot_puuid);
    }

    protected function tier(): Attribute
    {
        return Attribute::get(fn (): ?LolTier => $this->riotProfile?->tier);
    }

    protected function rank(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->riotProfile?->rank);
    }

    protected function lp(): Attribute
    {
        return Attribute::get(fn (): ?int => $this->riotProfile?->lp);
    }

    public function initials(): string
    {
        return Str::of($this->username)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function riotProfile(): HasOne
    {
        return $this->hasOne(RiotProfile::class);
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_members')
            ->withPivot('roleInTeam', 'roleInGame', 'joined_at')
            ->withTimestamps();
    }

    public function teamInvitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class);
    }

    public function teamApplications(): HasMany
    {
        return $this->hasMany(TeamApplication::class);
    }

    public function currentTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'current_team_id');
    }

    public function canManageCurrentTeam(): bool
    {
        if ($this->current_team_id === null) {
            return false;
        }

        $teamMember = currentMember();
        $teamMember = TeamMember::query()
            ->where('user_id', $this->id)
            ->where('team_id', $this->current_team_id)
            ->first();

        return $teamMember?->isCoachOrStaff() ?? false;
    }
}
