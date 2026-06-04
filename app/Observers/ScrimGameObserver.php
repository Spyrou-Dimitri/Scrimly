<?php

namespace App\Observers;

use App\Models\ScrimGame;
use App\Enums\StatusScrim;

class ScrimGameObserver
{
    public function created(ScrimGame $scrimGame): void
    {
        if ($scrimGame->scrim->status === StatusScrim::SCHEDULED) {
            $scrimGame->scrim->update([
                'status' => StatusScrim::IN_PROGRESS,
            ]);
        }
    }
}
