<?php

use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Events\MessageSent;

new #[Layout('layouts::team')] class extends Component
{
    public string $content = '';

    public Collection $chatMessages;

    public function mount(): void
    {
        $this->chatMessages = $this->loadChatMessages();
    }

    public function loadChatMessages(): Collection
    {
        return Message::query()
            ->with('teamMember.user')
            ->whereHas('teamMember', fn($query) => $query->where('team_id', currentTeam()->id))
            ->oldest()
            ->get();
    }

    public function sendMessage(): void
    {
        $this->validate([
            'content' => ['required', 'string', 'max:2000'],
        ]);

        $message = Message::query()->create([
            'content' => $this->content,
            'team_member_id' => currentMember()->id,
        ]);

        broadcast(new MessageSent($message, currentTeam()->id))->toOthers();

        $this->content = '';

        $message->load('teamMember.user');
        $this->chatMessages->push($message);
        $this->dispatch('toast', [
            'type' => 'success',
            'message' => __('pages/chats/index.sent_toast'),
        ]);
        $this->dispatch('scroll-to-the-end');
    }
    public function getListeners(): array
    {
        return [
            'echo-presence:chat.' . currentTeam()->id . ',.message.sent' => 'onMessageReceived',
        ];
    }

    public function onMessageReceived($event): void
    {
        $messageId = $event['messageId'];

        $message = Message::query()->with('teamMember.user')->find($messageId);
        $this->chatMessages->push($message);
        $this->dispatch('scroll-to-the-end');
    }
};
?>

<div
    class="-mx-6 -my-8 flex h-[calc(100dvh-4rem)] max-h-[calc(100dvh-4rem)] flex-col gap-6 overflow-hidden px-6 py-8"
    x-data="chat(
        {{ currentTeam()->id }},
        {{ currentMember()->user_id }},
        @js([
            'isOnline' => __('pages/chats/index.is_online'),
            'isOffline' => __('pages/chats/index.is_offline'),
            'onlineSuffix' => __('pages/chats/index.online_suffix'),
            'onlineAndOneOther' => __('pages/chats/index.online_and_one_other'),
            'onlineAndOthers' => __('pages/chats/index.online_and_others'),
        ])
    )">
    <div class="flex shrink-0 items-center justify-between gap-4">
        <h2 id="chats-index-heading" class="text-[32px] font-bold text-white">
            {{ __('pages/chats/index.title') }}
        </h2>

        <div
            x-show="onlineMembers.length > 0"
            x-cloak
            class="flex min-w-0 items-center gap-3"
            aria-live="polite">
            <div class="flex shrink-0 items-center">
                <template x-for="(member, index) in previewOnlineMembers" :key="member.id">
                    <div
                        class="relative size-9 shrink-0 overflow-hidden rounded-md"
                        :class="{ '-ml-2': index > 0 }"
                        :style="`z-index: ${index + 1}`">
                        <img
                            :src="member.avatar_url"
                            :alt="member.username"
                            class="size-full object-cover rounded-full" />
                        <span
                            x-show="index === previewOnlineMembers.length - 1"
                            class="absolute bottom-0 right-0 size-2 rounded-full bg-green-500 ring-1 ring-bg-main"
                            aria-hidden="true"></span>
                    </div>
                </template>
            </div>

            <p class="min-w-0 truncate text-sm leading-snug">
                <span class="font-semibold text-white" x-text="previewOnlineNames"></span><span class="text-text-secondary" x-text="onlineStatusSuffix"></span>
            </p>
        </div>
    </div>

    <section
        class="flex min-h-0 flex-1 flex-col overflow-hidden bg-bg-widget shadow-basic"
        aria-labelledby="chats-index-heading">
        <div
            x-data="{
        scroll() {
            this.$nextTick(() => {
                this.$el.scrollTo(0, this.$el.scrollHeight);
            });
        }
    }"
            x-init="scroll()"
            x-on:scroll-to-the-end.window="scroll()"

            class="min-h-0 flex-1 overflow-y-auto overscroll-contain p-6">
            @if ($this->chatMessages->isEmpty())
            <p class="flex h-full min-h-[12rem] items-center justify-center text-center text-text-secondary">
                {{ __('pages/chats/index.empty') }}
            </p>
            @else
            <ul class="flex flex-col gap-6" role="list">
                @foreach ($this->chatMessages as $message)
                @php
                $isOwnMessage = $message->team_member_id === currentMember()->id;
                $author = $message->teamMember->user;
                @endphp
                <li
                    wire:key="chat-message-{{ $message->id }}"
                    @class([ 'flex gap-3' , 'flex-row-reverse'=> $isOwnMessage,
                    ])>
                    <x-user-avatar
                        :user="$author"
                        preset="thumbnail"
                        class="size-10 shrink-0" />

                    <div @class([ 'flex min-w-0 max-w-[min(100%,36rem)] flex-col gap-1' , 'items-end'=> $isOwnMessage,
                        ])>
                        <p class="text-xs text-text-secondary">
                            @if ($isOwnMessage)
                            {{ __('pages/chats/index.you') }}
                            @elseif ($author)
                            {{ $author->username }}
                            @endif
                            <time datetime="{{ $message->created_at->toIso8601String() }}">
                                {{ $message->created_at->format('H:i') }}
                            </time>
                        </p>
                        <p @class([ 'w-full px-4 py-3 text-sm leading-relaxed' , 'bg-gold text-black'=> $isOwnMessage,
                            'bg-bg-card text-white' => ! $isOwnMessage,
                            ])>
                            {{ $message->content }}
                        </p>
                    </div>
                </li>
                @endforeach
            </ul>
            @endif
        </div>

        <p
            class="h-5 px-6 mb-2 text-sm italic text-text-secondary"
            x-show="typingLabel !== ''"
            x-text="typingLabel"
            aria-live="polite"></p>
        <form
            wire:submit.prevent="sendMessage"
            x-on:keydown.throttle.500ms="notifyTyping(@js(currentMember()->user->username))"
            class="sticky bottom-0 z-10 shrink-0 border-t border-input-border bg-bg-widget px-4 pb-6 pt-4 sm:px-6">

            <div class="flex flex-col gap-3 sm:flex-row sm:items-stretch sm:gap-4">

                <div class="min-w-0 flex-1">
                    <x-forms.input
                        wire:model="content"
                        :type="'text'"
                        :srOnlyLabel="true"
                        :name="'chat-message-body'"
                        :label="__('pages/chats/index.input_placeholder')"
                        :placeholder="__('pages/chats/index.input_placeholder')">
                        @error('content')
                        <p class="text-sm font-bold text-red-500">{{ $message }}</p>
                        @enderror
                    </x-forms.input>
                </div>
                <x-forms.submit class="shrink-0 sm:self-end">
                    {{ __('pages/chats/index.send') }}
                </x-forms.submit>
            </div>
        </form>
    </section>
</div>