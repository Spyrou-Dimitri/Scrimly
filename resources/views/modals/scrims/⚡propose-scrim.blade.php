<?php

use App\Models\Team;
use Livewire\Component;
use App\Livewire\Forms\CreateScrimRequestForm;

new class extends Component
{
    public Team $team;

    public CreateScrimRequestForm $form;
    public function mount(int $model_id): void
    {
        $this->team = Team::query()->findOrFail($model_id);
    }

    public function closeModal(): void
    {
        $this->dispatch('close_modal');
    }

    public function proposeScrim(): void {
        $this->form->store($this->team->id);
        $this->dispatch('close_modal');
        $this->dispatch('refresh_scrims');
        $this->dispatch('toast', [
            'title' => __('modals/scrims/propose-scrim.success'),
            'message' => __('modals/scrims/propose-scrim.success_message'),
            'type' => 'success',
        ]);
    }
};
?>

<div class="w-full">
    <x-layout.head-modal :width="'3xl'" :title="__('modals/scrims/propose-scrim.title') . ' ' . $team->name">
        <form wire:submit="proposeScrim" class="flex w-full flex-col gap-6 pt-2" wire:click.stop>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <x-forms.input
                    class="cursor-pointer"
                    wire:model.live="form.scrimDate"
                    name="scrim-date"
                    type="date"
                    :label="__('modals/scrims/propose-scrim.date')"
                    :required="true">
                    @error('form.scrimDate')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </x-forms.input>
                <x-forms.input
                    class="cursor-pointer"
                    wire:model.live="form.scrimTime"
                    name="scrim-time"
                    type="time"
                    :label="__('modals/scrims/propose-scrim.time')"
                    :required="true">
                    @error('form.scrimTime')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </x-forms.input>
            </div>

            <div class="flex flex-col gap-2 w-full">
                <p class="block font-medium text-white">
                    {{ __('modals/scrims/propose-scrim.game_count') }}
                    <span class="text-gold font-bold">*</span>
                </p>
                <div class="grid grid-cols-7 gap-2 sm:gap-6">
                    @foreach (range(2, 8) as $count)
                        <label class="relative w-full cursor-pointer col-span-1">
                            <input
                                type="radio"
                                name="game_count"
                                value="{{ $count }}"
                                wire:model.live="form.gameCount"
                                class="peer sr-only"
                                required
                            >
                            <div
                                class="
                                    flex min-h-11 min-w-11 items-center justify-center
                                    px-4 py-2 font-medium
                                    bg-bg-card text-white
                                    border border-transparent
                                    transition-all duration-200
                                    peer-checked:bg-gold peer-checked:text-black
                                    peer-focus-visible:ring-2 peer-focus-visible:ring-gold-light
                                    hover:opacity-80
                                ">
                                {{ $count }}
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <x-forms.textarea
                wire:model.live="form.message"
                name="scrim-message"
                :label="__('modals/scrims/propose-scrim.message')"
                :placeholder="__('modals/scrims/propose-scrim.message_placeholder')"
                :rows="5">
                    @error('message')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </x-forms.textarea>
            <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-between sm:pt-4">
                <button
                    type="button"
                    wire:click="closeModal"
                    title="{{ __('modals/scrims/propose-scrim.cancel') }}"
                    class="cta-secondary w-full sm:w-auto">
                    {{ __('modals/scrims/propose-scrim.cancel') }}
                </button>
                <button
                    type="submit"
                    title="{{ __('modals/scrims/propose-scrim.submit') }}"
                    class="cta-primary w-full cursor-pointer sm:w-auto">
                    {{ __('modals/scrims/propose-scrim.submit') }}
                </button>
            </div>
        </form>
    </x-layout.head-modal>
</div>
