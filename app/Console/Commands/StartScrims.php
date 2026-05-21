<?php

namespace App\Console\Commands;

use App\Enums\StatusScrim;
use App\Models\Scrim;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:start-scrims')]
#[Description('Passe les scrims en cours si la date de planification est atteinte est atteinte')]
class StartScrims extends Command
{
    public function handle()
    {
        $scrims = Scrim::query()
            ->where('status', StatusScrim::SCHEDULED)
            ->whereRaw('TIMESTAMP(scheduled_date, scheduled_time) <= ?', [now()])
            ->orderBy('scheduled_date')
            ->orderBy('scheduled_time')
            ->orderBy('id')
            ->get();

        foreach ($scrims as $scrim) {
            $scrim->update(['status' => StatusScrim::IN_PROGRESS]);
        }

        $this->info('Scrims en cours : '.Scrim::where('status', StatusScrim::IN_PROGRESS)->count());
    }
}
