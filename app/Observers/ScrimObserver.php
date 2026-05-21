<?php

namespace App\Observers;

use App\Enums\StatusScrim;
use App\Models\Scrim;

class ScrimObserver
{
    /**
     * Handle the Scrim "created" event.
     */
    public function created(Scrim $scrim): void
    {
        //
    }

    /**
     * Handle the Scrim "updated" event.
     */
    public function updated(Scrim $scrim): void
    {

        if ($scrim->wasChanged('status') && $scrim->status === StatusScrim::IN_PROGRESS) {
            Scrim::query()
                ->where('team_id', $scrim->team_id)
                ->where('status', StatusScrim::IN_PROGRESS)
                ->where('id', '!=', $scrim->id)
                ->update(['status' => StatusScrim::COMPLETED]);
        }

    }

    /**
     * Handle the Scrim "deleted" event.
     */
    public function deleted(Scrim $scrim): void
    {
        //
    }

    /**
     * Handle the Scrim "restored" event.
     */
    public function restored(Scrim $scrim): void
    {
        //
    }

    /**
     * Handle the Scrim "force deleted" event.
     */
    public function forceDeleted(Scrim $scrim): void
    {
        //
    }
}
