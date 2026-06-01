<?php

namespace App\Livewire\Forms;

use App\Enums\DefaultTeam;
use App\Enums\Language;
use App\Enums\LolGoal;
use App\Enums\LolServeur;
use App\Enums\RoleInGame;
use App\Enums\RoleInTeam;
use App\Enums\StatusInTeam;
use App\Jobs\ProcessUploadImageLogoTeam;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class CreateTeamForm extends Form
{
    #[Validate]
    public string $team_name = '';

    #[Validate]
    public string $tag = '';

    #[Validate]
    public ?LolServeur $server = null;

    #[Validate]
    public ?LolGoal $goal = null;

    #[Validate]
    public ?Language $language = null;

    #[Validate]
    public ?RoleInTeam $roleInTeam = null;

    #[Validate]
    public ?RoleInGame $roleInGame = null;

    #[Validate]
    public string $description = '';

    #[Validate]
    public $logo = null;

    #[Validate]
    public string $default_logo = 'Demacia';

    public function updatedRoleInTeam(): void
    {
        if ($this->roleInTeam !== RoleInTeam::PLAYER) {
            $this->roleInGame = null;
        }
    }

    protected function rules(): array
    {
        return [
            'team_name' => ['required', 'string', 'min:3', 'max:30', 'unique:teams,name'],
            'tag' => ['required', 'string', 'min:2', 'max:4', 'alpha_num', 'unique:teams,tag'],
            'server' => ['required', Rule::enum(LolServeur::class)],
            'goal' => ['required', Rule::enum(LolGoal::class)],
            'language' => ['required', Rule::enum(Language::class)],
            'roleInTeam' => ['required', Rule::enum(RoleInTeam::class)],
            'roleInGame' => ['nullable', Rule::requiredIf($this->roleInTeam === RoleInTeam::PLAYER), Rule::enum(RoleInGame::class)],
            'description' => ['nullable', 'string', 'max:1000'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'default_logo' => ['required', Rule::enum(DefaultTeam::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'team_name' => 'pages/team/create.team_name',
            'tag' => 'pages/team/create.tag',
            'server' => 'pages/team/create.server',
            'goal' => 'pages/team/create.goal',
            'language' => 'pages/team/create.language',
            'roleInTeam' => 'pages/team/create.roleInTeam',
            'description' => 'pages/team/create.description',
            'logo' => 'pages/team/create.logo',
            'default_logo' => 'pages/team/create.default_logo',
        ];
    }

    public function store(bool $applyPresetLogo): void
    {
        $validated = $this->validate();

        if ($validated['logo']) {
            $extension = $validated['logo']->extension() ?: $validated['logo']->getClientOriginalExtension();
            $new_original_file_name = uniqid().'.'.$extension;
            $full_path_to_original = Storage::disk(config('logoTeam.disk'))->putFileAs(
                config('logoTeam.original_path'),
                $validated['logo'],
                $new_original_file_name
            );
            if ($full_path_to_original) {
                $validated['logo'] = $new_original_file_name;
                ProcessUploadImageLogoTeam::dispatchSync($full_path_to_original, $new_original_file_name);
            } else {
                $validated['logo'] = '';
            }
        }

        if ($applyPresetLogo) {
            $logoType = 'default';
            $logoValue = DefaultTeam::from($validated['default_logo'])->value;
        } else {
            $logoType = 'upload';
            $logoValue = $validated['logo'];
        }

        if ($validated['roleInTeam'] === RoleInTeam::PLAYER) {
            $starterAverageElo = Auth::user()->riotProfile?->eloScore();
        } else {
            $starterAverageElo = null;
        }

        $team = Team::create([
            'name' => $validated['team_name'],
            'slug' => Str::slug($validated['team_name']),
            'tag' => $validated['tag'],
            'logo_type' => $logoType,
            'logo_value' => $logoValue,
            'description' => $validated['description'],
            'starter_average_elo' => $starterAverageElo,
            'language' => $validated['language'],
            'server' => $validated['server'],
            'goal' => $validated['goal'],
            'creator_id' => Auth::id(),
        ]);

        $team->members()->attach(Auth::id(), [
            'roleInTeam' => $validated['roleInTeam'],
            'roleInGame' => $validated['roleInGame'] ?? null,
            'joined_at' => now(),
            'status' => StatusInTeam::ACCEPTED,
        ]);
    }
}
