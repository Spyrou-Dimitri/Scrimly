@props(['task'])
@use('App\Enums\StatusTask')
@use('Carbon\Carbon')


<article
    {{ $attributes->merge([
        'class' => 'cursor-pointer relative bg-bg-widget p-6 shadow-basic border-l-2 '.$task->status->borderColor().' card-animated-border',
        'data-task-status' => $task->status->value,
    ]) }}>
    <span class="card-animated-border-right-edge" aria-hidden="true"></span>
    <div class="relative z-[1] flex flex-col gap-6">
        <h4 class="text-xl font-bold text-gold">{{ $task->title }}</h4>
        <div class="flex flex-col gap-3">
            <div class="flex justify-between gap-2">
                <p class="text-text-secondary">
                    Tâches : {{ $task->subtasks->where('is_completed', true)->count() }} / {{ $task->subtasks->count() }}
                </p>
                <p class="text-text-secondary">
                    {{ $task->subtasks->count() > 0 ? round($task->subtasks->where('is_completed', true)->count() / $task->subtasks->count() * 100) : 0 }}%
                </p>

            </div>
            <div class="w-full bg-gray-200  h-2.5">
                <div class="{{ $task->status->backgroundColor() }} h-2.5" style="width: {{ $task->subtasks->count() > 0 ? $task->subtasks->where('is_completed', true)->count() / $task->subtasks->count() * 100 : 0 }}%"></div>
            </div>
        </div>
        <div class="flex gap-4 flex-wrap justify-between">
            <div class="flex gap-4 items-center">
                <div class="flex items-center gap-2">
                    <flux:icon name="chat-bubble-left-ellipsis" class="w-5 h-5" />
                    {{ $task->comments->count() }}
                </div>
                <div class="flex items-center gap-2">
                    <flux:icon name="paper-clip" class="w-5 h-5" />
                    {{ $task->links->count() }}
                </div>
            </div>
            <p>
                @if ($task->status === StatusTask::DONE)
                Terminé : {{ $task->completed_at->translatedFormat('d M Y') }}
                @else
                Echéance : @if($task->deadline) {{ $task->deadline->translatedFormat('d M Y') }} @else -
                @endif
                @endif
            </p>
        </div>
    </div>
</article>