<?php

use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

new #[Layout('layouts::choose_a_team')] class extends Component
{
    public User $currentUser;
    public function mount()
    {
        $this->currentUser = Auth::user();
    }
};
?>

<div class="font-spaceGrotesk">
    <section class="flex flex-col items-center gap-6 justify-center">
        <div>
            <h2 class="text-[40px] font-bold text-center">
                {{ __('pages/team/index.title') }} <span class="text-gold font-bold">{{$currentUser->username}}</span>
            </h2>
            <p class="text-center text-2xl">
                {{ __('pages/team/index.description') }}
            </p>
        </div>
        <div class="flex flex-row gap-4">
            <x-cta :href="route('team.create')" :title="__('pages/team/index.join_team_title')" :class="'secondary'">
                {{ __('pages/team/index.join_team_cta') }}
            </x-cta>
            <x-cta :href="route('team.create')" :title="__('pages/team/index.create_team_title')" :class="'primary'">
                {{ __('pages/team/index.create_team_cta') }}
            </x-cta>

        </div>

    </section>
</div>