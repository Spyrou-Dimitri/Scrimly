<?php

namespace App\Livewire\Forms;

use App\Enums\RoleInGame;
use App\Enums\RoleInTeam;
use App\Enums\StatusInvitation;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class CreateInvitationTeamForm extends Form
{
    #[Validate]
    public string $username = '';

    #[Validate]
    public ?RoleInTeam $roleInTeam = null;

    #[Validate]
    public ?RoleInGame $roleInGame = null;

    #[Validate]
    public string $motivation = '';

    protected function rules(): array
    {
        return [
            'username' => ['required', 'string', 'min:3', 'max:20', 'exists:users,username'],
            'roleInTeam' => ['required', Rule::enum(RoleInTeam::class)],
            'roleInGame' => ['nullable', Rule::requiredIf($this->roleInTeam === RoleInTeam::PLAYER), Rule::enum(RoleInGame::class)],
            'motivation' => ['required', 'string', 'max:1000'],
        ];
    }

    protected function attributes(): array
    {
        return [
            'username' => __('pages/roster/invitations.username'),
            'roleInTeam' => __('pages/roster/invitations.roleInTeam'),
            'roleInGame' => __('pages/roster/invitations.roleInGame'),
            'motivation' => __('pages/roster/invitations.motivation'),
        ];
    }

    public function store(Team $team, User $user): bool
    {
        $this->validate();

        $authorization = Gate::inspect('create', [TeamInvitation::class, $team, $user]);
        if ($authorization->denied()) {
            $this->addError('error', $authorization->message());

            return false;
        }

        $user->teamInvitations()->create([
            'status' => StatusInvitation::PENDING,
            'roleInTeam' => $this->roleInTeam,
            'roleInGame' => $this->roleInGame,
            'motivation' => $this->motivation,
            'team_id' => $team->id,
        ]);

        return true;
    }
}
