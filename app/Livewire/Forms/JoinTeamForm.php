<?php

namespace App\Livewire\Forms;

use App\Enums\RoleInGame;
use App\Enums\RoleInTeam;
use App\Enums\StatusApplication;
use App\Models\Team;
use App\Models\TeamApplication;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class JoinTeamForm extends Form
{
    #[Validate]
    public string $team_code = '';

    #[Validate]
    public ?RoleInTeam $roleInTeam = null;

    #[Validate]
    public ?RoleInGame $roleInGame = null;

    #[Validate]
    public string $motivation = '';

    protected function rules(): array
    {
        return [
            'team_code' => ['required', 'string', 'max:6', 'exists:teams,code'],
            'roleInTeam' => ['required', Rule::enum(RoleInTeam::class)],
            'roleInGame' => ['nullable', Rule::requiredIf($this->roleInTeam === RoleInTeam::PLAYER), Rule::enum(RoleInGame::class)],
            'motivation' => ['required', 'string', 'max:1000'],
        ];
    }

    protected function attributes(): array
    {
        return [
            'team_code' => 'Code de l\'équipe',
            'roleInTeam' => 'Rôle dans l\'équipe',
            'roleInGame' => 'Rôle dans le jeu',
            'motivation' => 'Motivation',
        ];
    }

    public function store(): void
    {
        $validated = $this->validate();

        $teamId = Team::where('code', $validated['team_code'])->first()->id;
        $userId = Auth::user()->id;

        $application = TeamApplication::create([
            'team_id' => $teamId,
            'user_id' => $userId,
            'roleInTeam' => $validated['roleInTeam'],
            'roleInGame' => $validated['roleInGame'],
            'motivation' => $validated['motivation'],
            'status' => StatusApplication::PENDING,
        ]);

        $application->save();
    }

    public function updatedRoleInTeam(): void
    {
        if ($this->roleInTeam !== RoleInTeam::PLAYER) {
            $this->roleInGame = null;
        }
    }
}
