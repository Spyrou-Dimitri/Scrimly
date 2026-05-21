<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Scrim;
use App\Enums\StatusScrim;
use Carbon\Carbon;

#[Signature('app:start-scrims')]
#[Description('Passe les scrims en cours si la date de planification est atteinte est atteinte')]
class StartScrims extends Command
{

    public function handle()
    {
        Scrim::query()
            ->where('status', StatusScrim::SCHEDULED)
            ->whereRaw('TIMESTAMP(scheduled_date, scheduled_time) <= ?', [now()])
            ->update(['status' => StatusScrim::IN_PROGRESS->value]);
        $this->info('Scrims en cours : ' . Scrim::where('status', StatusScrim::IN_PROGRESS->value)->count());
    }
}
