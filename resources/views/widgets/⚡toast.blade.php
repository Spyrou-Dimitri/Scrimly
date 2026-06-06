<?php

use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    public array $toasts = [];

    public function mount(): void
    {
        if (session()->has('toast')) {
            $this->add(session()->get('toast'));
        }
    }

    #[On('toast')]
    public function add(array $payload): void
    {
        $this->toasts[] = [
            'id' => uniqid('toast_', true),
            'type' => $payload['type'] ?? 'info',
            'message' => $payload['message'],
            'duration' => $payload['duration'] ?? 5000,
        ];
    }

    public function remove(string $id): void
    {
        $this->toasts = array_values(
            array_filter($this->toasts, fn($t) => $t['id'] !== $id)
        );
    }
};
?>

<div
    class="fixed top-20 right-8 z-90 flex flex-col gap-2"
    aria-live="polite"
    aria-atomic="true">
    @foreach($toasts as $toast)
    <div
        role="status"
        wire:key="{{ $toast['id'] }}"
        x-data="{ show: false }"
        x-init="
                $nextTick(() => show = true);
                setTimeout(() => {
                    show = false;
                    setTimeout(() => $wire.remove('{{ $toast['id'] }}'), 300);
                }, {{ $toast['duration'] }});
            "
        x-show="show"
        x-transition:enter="transition ease-in-out duration-150"
        x-transition:enter-start="opacity-0 translate-x-12"
        x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="transition ease-in-out duration-150"
        x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 translate-x-12"
        class="px-4 bg-bg-widget py-3 shadow-basic min-w-[280px]">
        <div class="flex items-center gap-2">
            @if($toast['type'] === 'success')
            <flux:icon.check-circle class="w-6 h-6 text-green-500" />
            @elseif($toast['type'] === 'wifi')
            <flux:icon.wifi class="w-6 h-6 text-green-500" />
            @elseif($toast['type'] === 'no-symbol')
            <flux:icon.no-symbol class="w-6 h-6 text-red-500" />
            @elseif($toast['type'] === 'error')
            <flux:icon.x-circle class="w-6 h-6 text-red-500" />
            @elseif($toast['type'] === 'trash')
            <flux:icon.trash class="w-6 h-6 text-red-500" />
            @else
            <flux:icon.information-circle class="w-6 h-6 text-blue-500" />
            @endif
            <span class="text-white">{{ $toast['message'] }}</span>
        </div>
    </div>
    @endforeach
</div>