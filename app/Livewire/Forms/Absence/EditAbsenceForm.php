<?php

namespace App\Livewire\Forms\Absence;

use App\Enums\AbsenceJustification;
use App\Models\Absence;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class EditAbsenceForm extends Form
{
    #[Validate]
    public string $date;

    #[Validate]
    public AbsenceJustification $justification;

    public Absence $absence;

    public function setAbsence(Absence $absence): void
    {
        $this->absence = $absence;
        $this->date = $absence->date->format('Y-m-d');
        $this->justification = $absence->justification;
    }

    protected function rules(): array
    {
        return [
            'date' => ['required', 'date', 'after_or_equal:today'],
            'justification' => ['required', Rule::enum(AbsenceJustification::class)],
        ];
    }

    public function saveAbsence(): void
    {
        $this->validate();
        $this->absence->update([
            'date' => $this->date,
            'justification' => $this->justification,
        ]);
    }
}
