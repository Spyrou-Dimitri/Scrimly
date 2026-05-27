<?php

use App\Enums\ScrimOutcome;
use App\Enums\StatusScrim;
use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scrims', function (Blueprint $table) {
            $table->id();
            $table->date('scheduled_date')->default(CarbonImmutable::now()->toDateString());
            $table->time('scheduled_time')->default(CarbonImmutable::now()->toTimeString());
            $table->tinyInteger('number_of_games')->default(1);
            $table->enum('status', StatusScrim::cases());
            $table->enum('outcome', ScrimOutcome::cases())->nullable();
            $table->text('summary')->nullable();
            $table->text('advantages')->nullable();
            $table->text('disadvantages')->nullable();
            $table->foreignId('scrim_request_id')->constrained('scrim_requests')->cascadeOnDelete();
            $table->foreignId('opponent_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scrims');
    }
};
