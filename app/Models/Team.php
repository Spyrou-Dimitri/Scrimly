<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\Language;
use App\Enums\LolServeur;
use App\Enums\LolGoal;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
class Team extends Model
{
    protected $fillable = [
        'name',
        'tag',
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

   

   protected function tag(): Attribute
   {
    return Attribute::make(
        get: fn ($value) => strtoupper($value),
        set: fn ($value) => strtoupper($value),
    );
   }

}
