<?php

namespace App\Livewire\Forms;

use App\Enums\Language;
use App\Enums\LolGoal;
use App\Enums\LolServeur;
use App\Enums\RoleInTeam;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;
use App\Models\TeamMember;
use App\Enums\RoleInGame;
use App\Jobs\ProcessUploadImageLogoTeam;
use Illuminate\Support\Facades\Storage;

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


    public function updatedRoleInTeam():void
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
        ];
    }


    public function store(): void
    {
        $validated = $this->validate();


        if ($validated['logo']) {
            $extension = $validated['logo']->extension() ?: $validated['logo']->getClientOriginalExtension();
            $new_original_file_name = uniqid() . '.' . $extension;
            $full_path_to_original = Storage::putFileAs(
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

        $team = Team::create([
            'name' => $validated['team_name'],
            'tag' => $validated['tag'],
            'logo' => $validated['logo'],
            'description' => $validated['description'],
            'language' => $validated['language'],
            'server' => $validated['server'],
            'goal' => $validated['goal'],
            'creator_id' => Auth::id(),
        ]);

        $team->members()->attach(Auth::id(), [
            'roleInTeam' => $validated['roleInTeam'],
            'roleInGame' => $validated['roleInGame'] ?? null,
            'joined_at' => now(),
        ]);
    }
}
