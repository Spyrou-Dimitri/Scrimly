<?php

use Livewire\Component;
use App\Models\Task;
use App\Enums\StatusTask;

new class extends Component
{
    public Task $task;

    public function mount(int $model_id): void
    {
        $this->task = Task::findOrFail($model_id);
    }

    public function completeTask(): void
    {
        $this->task->update(['status' => StatusTask::DONE, 'completed_at' => now()]);
        $this->dispatch('close_modal');
        $this->dispatch('refresh_tasks');
        $this->dispatch('toast', [
            'title' => __('modals/tasks/complete-task.success_title'),
            'message' => __('modals/tasks/complete-task.success_message'),
            'type' => 'check',
        ]);
    }
};
?>

<div class="w-full">
    <x-layout.head-modal :title="__('modals/tasks/complete-task.title')">
        <form wire:submit.prevent="completeTask" class="flex w-full flex-col gap-6 pt-2">
            <div class="flex w-full flex-col gap-3">
                <div
                    class="mx-auto flex size-14 shrink-0 items-center justify-center rounded-none bg-gold/10 ring-1 ring-gold/40"
                    aria-hidden="true">
                    <flux:icon name="check" class="size-8 text-gold" />
                </div>

                <p class="text-center text-2xl font-bold text-text-primary">
                    {{ __('modals/tasks/complete-task.body_heading') }}
                </p>
                <p class="text-center text-base font-normal text-text-secondary">
                    {{ __('modals/tasks/complete-task.legend_form') }}
                </p>
            </div>

            <div class="flex w-full flex-wrap justify-center gap-3 sm:justify-between">
                <x-cta :href="route('tasks.show', ['slug' => currentTeam()->slug, 'id' => $task->id])" :class="'secondary'" :title="__('modals/tasks/complete-task.cancel_button')">
                    {{ __('modals/tasks/complete-task.cancel_button') }}
                </x-cta>
                <x-forms.submit>
                    {{ __('modals/tasks/complete-task.confirm_button') }}
                </x-forms.submit>
            </div>
        </form>
    </x-layout.head-modal>
</div>