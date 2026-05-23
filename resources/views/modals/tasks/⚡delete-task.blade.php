<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

new class extends Component
{
    public Task $task;

    public function mount(int $model_id): void
    {
        $task = Task::findOrFail($model_id);
        abort_unless(
            $task->team_id === currentTeam()->id
                && Gate::allows('manageTeam', User::class),
            403,
        );

        $this->task = $task;
    }

    public function closeModal(): void
    {
        $this->dispatch('close_modal');
    }

    public function deleteTask(): void
    {
        $this->task->delete();
        if (request()->route()->named('tasks.index')) {
            $this->dispatch('close_modal');
            $this->dispatch('refresh_tasks');
            $this->dispatch('toast', [
                'title' => __('modals/tasks/delete-task.success_title'),
                'message' => __('modals/tasks/delete-task.success_message'),
                'type' => 'trash',
            ]);
        } else {
            session()->flash('toast', [
                'type' => 'success',
                'message' => __('modals/tasks/delete-task.success_message'),
            ]);
            redirect()->route('tasks.index', ['slug' => currentTeam()->slug]);

        }
    }
};
?>

<div>
    <x-layout.head-modal :title="__('modals/tasks/delete-task.title').' '.$this->task->title" :destroy="true">
        <form wire:submit.prevent="deleteTask" class="flex flex-col items-center gap-6 pt-2 text-center">
            <div
                class="flex size-14 shrink-0 items-center justify-center rounded-none bg-red-950/40 ring-1 ring-red-900/60"
                aria-hidden="true">
                <flux:icon name="trash" class="size-8 text-red-700/90" />
            </div>

            <div class="flex max-w-sm flex-col gap-2">
                <p class="text-2xl font-bold text-text-primary">
                    {{ __('modals/tasks/delete-task.body_heading') }}
                </p>
                <p class="text-base font-normal text-text-secondary">
                    {{ __('modals/tasks/delete-task.legend_form') }}
                </p>
            </div>

            <div class="flex w-full max-w-md justify-center gap-3 sm:justify-between">
                <button
                    wire:click.prevent="closeModal"
                    type="button"
                    title="{{ __('modals/tasks/delete-task.cancel_button') }}"
                    class="cta-secondary">
                    {{ __('modals/tasks/delete-task.cancel_button') }}
                </button>
                <button
                    type="submit"
                    title="{{ __('modals/tasks/delete-task.confirm_button') }}"
                    class="cursor-pointer rounded-none border border-red-900 bg-red-950/70 px-4 py-2 font-bold text-white transition-colors hover:bg-red-900/90">
                    {{ __('modals/tasks/delete-task.confirm_button') }}
                </button>
            </div>
        </form>
    </x-layout.head-modal>
</div>