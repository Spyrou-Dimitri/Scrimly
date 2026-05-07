<?php

namespace App\Livewire\Forms\Absence;

use App\Enums\AbsenceJustification;
use App\Models\Absence;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class AddAbsenceForm extends Form
{
    #[Validate]
    public string $absenceDay = '';

    #[Validate]
    public ?AbsenceJustification $justification = null;

    protected function rules(): array
    {
        return [
            'absenceDay' => ['required', 'date', 'after_or_equal:today'],
            'justification' => ['required', Rule::enum(AbsenceJustification::class)],
        ];
    }

    public function store(int $teamMemberId): void
    {
        $this->validate();

        Absence::create([
            'team_member_id' => $teamMemberId,
            'date' => $this->absenceDay,
            'justification' => $this->justification,
        ]);
    }
}
