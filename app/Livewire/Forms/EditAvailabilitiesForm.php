<?php

namespace App\Livewire\Forms;

use App\Enums\DayOfTheWeek;
use App\Models\PlayerDefaultSchedule;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class EditAvailabilitiesForm extends Form
{
    #[Validate]
    public array $slotEnabled = [];

    #[Validate]
    public array $startTimes = [];

    #[Validate]
    public array $endTimes = [];

    public function rules(): array
    {
        $rules = [
            'slotEnabled' => ['nullable', 'array'],
            'slotEnabled.*' => ['boolean'],
            'startTimes' => ['nullable', 'array'],
            'endTimes' => ['nullable', 'array'],
        ];

        foreach (DayOfTheWeek::cases() as $day) {
            $key = (string) $day->value;
            $dayValue = $day->value;

            $rules["startTimes.{$key}"] = [
                Rule::requiredIf(fn () => (bool) ($this->slotEnabled[$dayValue] ?? false)),
                'nullable',
                'date_format:H:i',
                'regex:/^(0[89]|1[0-9]|2[0-3]):(00|30)$/',
                'before:endTimes.{$key}',
            ];

            $rules["endTimes.{$key}"] = [
                Rule::requiredIf(fn () => (bool) ($this->slotEnabled[$dayValue] ?? false)),
                'nullable',
                'date_format:H:i',
                'regex:/^(0[89]|1[0-9]|2[0-3]):(00|30)$/',
                "after:startTimes.{$key}",
            ];
        }

        return $rules;
    }

    public function saveAvailabilities(int $teamMemberId): void
    {
        $this->validate();

        foreach (DayOfTheWeek::cases() as $day) {
            $dayValue = $day->value;
            $enabled = (bool) ($this->slotEnabled[$dayValue] ?? false);

            if ($enabled) {
                PlayerDefaultSchedule::updateOrCreate(
                    [
                        'team_member_id' => $teamMemberId,
                        'day_of_week' => $dayValue,
                    ],
                    [
                        'start_time' => $this->startTimes[$dayValue],
                        'end_time' => $this->endTimes[$dayValue],
                    ],
                );
            } else {
                PlayerDefaultSchedule::query()
                    ->where('team_member_id', $teamMemberId)
                    ->where('day_of_week', $dayValue)
                    ->delete();
            }
        }
    }
}
