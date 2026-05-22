<?php

namespace App\Livewire\Forms;

use App\Enums\StatusScrimRequest;
use App\Models\ScrimRequest;
use App\Models\Team;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Validate;
use Livewire\Form;

class CreateScrimRequestForm extends Form
{
    #[Validate]
    public string $scrimDate = '';

    #[Validate]
    public string $scrimTime = '';

    #[Validate]
    public int $gameCount = 0;

    #[Validate]
    public string $message = '';

    public function rules(): array
    {
        return [
            'scrimDate' => ['required', 'date', 'after_or_equal:today'],
            'scrimTime' => ['required', 'date_format:H:i'],
            'gameCount' => ['required', 'integer', 'min:2', 'max:8'],
            'message' => ['nullable', 'string', 'min:3', 'max:1000'],
        ];
    }

    public function store(int $teamId): bool
    {
        $validated = $this->validate();

        $receiverTeam = Team::query()->findOrFail($teamId);

        if (Gate::denies('create', [ScrimRequest::class, $receiverTeam])) {
            $this->addError('error', __('modals/scrims/propose-scrim.duplicate_pending'));

            return false;
        }

        ScrimRequest::create([
            'requester_team_id' => currentTeam()->id,
            'receiver_team_id' => $teamId,
            'status' => StatusScrimRequest::PENDING,
            'scheduled_date' => $validated['scrimDate'],
            'scheduled_time' => $validated['scrimTime'],
            'number_of_games' => $validated['gameCount'],
            'message' => $validated['message'],
        ]);

        return true;
    }
}
