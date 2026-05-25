<?php

namespace App\Livewire\Forms\Calendar;

use App\Enums\TypeEvents;
use App\Models\Event;
use Illuminate\Validation\Rule;
use Livewire\Form;

class CreateEventForm extends Form
{
    public string $title = '';

    public bool $all_day = false;

    public string $start_time = '';

    public string $end_time = '';

    public ?TypeEvents $type = null;

    
    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:50'],
            'all_day' => ['required', 'boolean'],
            'start_time' => [
                Rule::requiredIf(fn (): bool => ! $this->all_day),
                'nullable',
                'date_format:H:i',
            ],
            'end_time' => [
                Rule::requiredIf(fn (): bool => ! $this->all_day),
                'nullable',
                'date_format:H:i',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($this->all_day || blank($this->start_time) || blank($value)) {
                        return;
                    }

                    if ($value <= $this->start_time) {
                        $fail(__('modals/calendar/create-event.end_time_after_start'));
                    }
                },
            ],
            'type' => ['required', Rule::enum(TypeEvents::class)],
        ];
    }

    
    protected function validationAttributes(): array
    {
        return [
            'title' => __('modals/calendar/create-event.field_title'),
            'all_day' => __('modals/calendar/create-event.all_day'),
            'start_time' => __('modals/calendar/create-event.start_time'),
            'end_time' => __('modals/calendar/create-event.end_time'),
            'type' => __('modals/calendar/create-event.type'),
        ];
    }

    public function updatedAllDay(bool $value): void
    {
        if ($value) {
            $this->start_time = '';
            $this->end_time = '';
        }
    }

    public function store(string $date, int $teamId): void
    {
        $validated = $this->validate();

        Event::create([
            'title' => $validated['title'],
            'date' => $date,
            'start_time' => $validated['all_day'] ? null : $validated['start_time'],
            'end_time' => $validated['all_day'] ? null : $validated['end_time'],
            'all_day' => $validated['all_day'],
            'type' => $validated['type'],
            'team_id' => $teamId,
        ]);
    }
}
