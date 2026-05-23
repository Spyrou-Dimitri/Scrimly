<?php

use App\Enums\StatusScrimRequest;
use App\Models\ScrimRequest;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\Scrim;
use App\Models\User;
use App\Enums\StatusScrim;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;


new class extends Component
{
    public ScrimRequest $scrimRequest;

    public function mount(int $model_id): void
    {
        $this->scrimRequest = ScrimRequest::with(['requesterTeam', 'receiverTeam'])
            ->findOrFail($model_id);

        $teamId = currentTeam()?->id;
        abort_unless(
            $teamId !== null
                && (
                    $teamId === $this->scrimRequest->requester_team_id
                    || $teamId === $this->scrimRequest->receiver_team_id
                ),
            403,
        );
    }

    public function acceptScrimRequest(): void
    {
        if (Gate::denies('manageTeam', User::class)) {
            $this->dispatch('toast', [
                'title' => __('policies/scrim.error_title'),
                'message' => __('policies/scrim.error_message'),
                'type' => 'error',
            ]);
            $this->dispatch('close_modal');
            return;
        }

        DB::transaction(function () {


            $this->scrimRequest->update([
                'status' => StatusScrimRequest::ACCEPTED,
            ]);

            $scrimForReceiverTeam = Scrim::create([
                'scheduled_date' => $this->scrimRequest->scheduled_date,
                'scheduled_time' => $this->scrimRequest->scheduled_time,
                'number_of_games' => $this->scrimRequest->number_of_games,
                'status' => StatusScrim::SCHEDULED,
                'scrim_request_id' => $this->scrimRequest->id,
                'opponent_team_id' => $this->scrimRequest->requester_team_id,
                'team_id' => currentTeam()?->id,
            ]);

            $scrimForRequesterTeam = Scrim::create([
                'scheduled_date' => $this->scrimRequest->scheduled_date,
                'scheduled_time' => $this->scrimRequest->scheduled_time,
                'number_of_games' => $this->scrimRequest->number_of_games,
                'status' => StatusScrim::SCHEDULED,
                'scrim_request_id' => $this->scrimRequest->id,
                'opponent_team_id' => currentTeam()?->id,
                'team_id' => $this->scrimRequest->requester_team_id,
            ]);
        });
        $this->dispatch('close_modal');
        $this->dispatch('refresh_scrims');
        $this->dispatch('toast', [
            'title' => __('modals/scrims/show-scrim-request.success_title'),
            'message' => __('modals/scrims/show-scrim-request.success_message'),
            'type' => 'success',
        ]);
    }

    public function refuseScrimRequest(): void
    {
        if (Gate::denies('manageTeam', User::class)) {
            $this->dispatch('toast', [
                'title' => __('policies/scrim.error_title'),
                'message' => __('policies/scrim.error_message'),
                'type' => 'error',
            ]);
            $this->dispatch('close_modal');
            return;
        }
        DB::transaction(function () {
            $this->scrimRequest->update([
                'status' => StatusScrimRequest::REJECTED,
            ]);
        });
        $this->dispatch('close_modal');
        $this->dispatch('refresh_scrims');
        $this->dispatch('toast', [
            'title' => __('modals/scrims/show-scrim-request.refuse_title'),
            'message' => __('modals/scrims/show-scrim-request.refuse_message'),
            'type' => 'trash',
        ]);
    }

    #[Computed]
    public function scheduledAt(): Carbon
    {
        return Carbon::parse(
            $this->scrimRequest->scheduled_date->format('Y-m-d') . ' ' . $this->scrimRequest->scheduled_time
        );
    }
};
?>

@php
$requesterTeam = $this->scrimRequest->requesterTeam;
@endphp

<div class="w-full">
    <x-layout.head-modal
        :width="'3xl'"
        :title="__('modals/scrims/show-scrim-request.title_with_team', [
            'title' => __('modals/scrims/show-scrim-request.title'),
            'team' => $requesterTeam->name,
        ])">
        <div class="flex flex-col gap-6 pt-1" wire:click.stop>
            <div class="flex flex-row items-center gap-4 border-b border-white/10 pb-5">
                <img
                    src="{{ $requesterTeam->logo_url }}"
                    alt="{{ $requesterTeam->name }}"
                    class="size-[4.5rem] shrink-0 rounded-sm object-cover ring-1 ring-white/15 md:size-[5rem]" />
                <div class="min-w-0 flex-1">
                    <p class="truncate text-xl font-bold text-text-primary md:text-2xl">
                        {{ $requesterTeam->name }}
                    </p>
                    <p class="mt-1 font-mono text-sm text-text-secondary">
                        [{{ $requesterTeam->tag }}]
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                {{-- Date & heure --}}
                <div
                    class="flex flex-col gap-3 bg-bg-widget p-4 shadow-basic">
                    <div class="flex items-center gap-2 text-xs font-semibold text-gold">
                        <flux:icon name="calendar-days" class="size-4 shrink-0" />
                        {{ __('modals/scrims/show-scrim-request.schedule_label') }}
                    </div>
                    <p class="text-sm font-medium capitalize text-text-primary">
                        {{ $this->scheduledAt->translatedFormat('l j F Y') }}
                    </p>
                    <p class="text-2xl font-bold tabular-nums tracking-tight text-text-primary md:text-[1.65rem]">
                        {{ $this->scheduledAt->format('H:i') }}
                    </p>
                </div>

                {{-- Format --}}
                <div class="flex flex-col gap-3 bg-bg-widget p-4 shadow-basic">
                    <div class="flex items-center gap-2 text-xs font-semibold text-gold">
                        <flux:icon name="scale" class="size-4 shrink-0" />
                        {{ __('modals/scrims/show-scrim-request.format_label') }}
                    </div>
                    <p class="text-xl font-bold text-text-primary">
                        {{ __('modals/scrims/show-scrim-request.format_games_line', ['count' => $this->scrimRequest->number_of_games]) }}
                    </p>
                    <p class="text-xs font-semibold text-text-secondary">
                        {{ __('modals/scrims/show-scrim-request.format_series_subtitle', ['count' => $this->scrimRequest->number_of_games]) }}
                    </p>
                </div>

                {{-- Statut --}}
                <div
                    class="flex flex-col gap-3 bg-bg-widget p-4 shadow-basic">
                    <div class="flex items-center gap-2 text-xs font-semibold text-gold">
                        <flux:icon name="clock" class="size-4 shrink-0" />
                        {{ __('modals/scrims/show-scrim-request.status_label') }}
                    </div>
                    <div class="rounded-sm bg-bg-widget p-3 p-3">
                        <p class="text-center text font-semibold">
                            {{ $this->scrimRequest->status->label() }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Message pleine largeur --}}
            <div class="flex flex-col gap-3 bg-bg-widget p-5 shadow-basic">
                <div class="flex items-center gap-2 text-[11px] font-semibold text-gold">
                    <flux:icon name="chat-bubble-left-ellipsis" class="size-4 shrink-0 text-gold" />
                    {{ __('modals/scrims/show-scrim-request.message_coach_label') }}
                </div>
                @if (filled($this->scrimRequest->message))
                <p class="text-base italic leading-relaxed text-text-primary">
                    {{ $this->scrimRequest->message }}
                </p>
                @else
                <p class="text-base italic text-text-secondary">
                    {{ __('modals/scrims/show-scrim-request.no_message') }}
                </p>
                @endif
            </div>
            <div class="flex pt-4 border-t border-white/10 flex-row gap-4 sm:items-center sm:justify-between sm:gap-6">
                <div class="flex flex-1 justify-stretch sm:justify-start">
                    <x-destructive
                        wire:click="refuseScrimRequest"
                        type="button"
                        wire:loading.attr="disabled"
                        class="cta-danger w-full whitespace-nowrap px-5 py-2.5 text-center text-xs font-bold sm:w-auto sm:min-w-[8.5rem]"
                        :title="__('modals/scrims/show-scrim-request.refuse')">
                        {{ __('modals/scrims/show-scrim-request.refuse') }}
                    </x-destructive>
                </div>

                <div class="flex flex-1 justify-stretch sm:justify-end">
                    <x-accept
                        wire:click="acceptScrimRequest"
                        type="button"
                        wire:loading.attr="disabled"
                        class="cta-success w-full whitespace-nowrap px-5 py-2.5 text-center text-xs font-bold sm:w-auto sm:min-w-[8.5rem]"
                        :title="__('modals/scrims/show-scrim-request.accept')">
                        {{ __('modals/scrims/show-scrim-request.accept') }}
                    </x-accept>
                </div>
            </div>
        </div>
    </x-layout.head-modal>
</div>