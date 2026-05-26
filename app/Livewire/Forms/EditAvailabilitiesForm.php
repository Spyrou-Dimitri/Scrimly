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
            $dayValue = $day->value;

            $rules["startTimes.{$dayValue}"] = [
                Rule::excludeIf(fn () => !($this->slotEnabled[$dayValue] ?? false)),
                'required',
                'date_format:H:i',
                'regex:/^(0[89]|1[0-9]|2[0-3]):(00|30)$/',
                "before:endTimes.{$dayValue}",
            ];

            $rules["endTimes.{$dayValue}"] = [
                Rule::excludeIf(fn () => !($this->slotEnabled[$dayValue] ?? false)),
                'required',
                'date_format:H:i',
                'regex:/^(0[89]|1[0-9]|2[0-3]):(00|30)$/',
                "after:startTimes.{$dayValue}",
            ];
        }

        return $rules;
    }

    protected function validationAttributes(): array
    {
        return [
            'startTimes.*' => __('modals/edit-availabilities.start_time'),
            'endTimes.*' => __('modals/edit-availabilities.end_time'),
        ];
    }

    protected function messages(): array
    {
        $messages = [];

        foreach (DayOfTheWeek::cases() as $day) {
            $dayValue = $day->value;

            $messages["startTimes.{$dayValue}.before"] = __('modals/edit-availabilities.start_time_before_end');
            $messages["endTimes.{$dayValue}.after"] = __('modals/edit-availabilities.end_time_after_start');
            $messages["startTimes.{$dayValue}.required"] = __('modals/edit-availabilities.start_time_required');
            $messages["endTimes.{$dayValue}.required"] = __('modals/edit-availabilities.end_time_required');
            $messages["startTimes.{$dayValue}.regex"] = __('modals/edit-availabilities.invalid_time');
            $messages["endTimes.{$dayValue}.regex"] = __('modals/edit-availabilities.invalid_time');
            $messages["startTimes.{$dayValue}.date_format"] = __('modals/edit-availabilities.invalid_time');
            $messages["endTimes.{$dayValue}.date_format"] = __('modals/edit-availabilities.invalid_time');
        }

        return $messages;
    }

    public function saveAvailabilities(int $teamMemberId): void
    {
        $this->validate();

        foreach (DayOfTheWeek::cases() as $day) {
            $dayValue = $day->value;
            $enabled =  ($this->slotEnabled[$dayValue] ?? false);

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
