<?php

use App\Enums\StatusScrimRequest;
use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scrim_requests', function (Blueprint $table) {
            $table->id();
            $table->enum('status', StatusScrimRequest::cases());
            $table->date('scheduled_date');
            $table->time('scheduled_time');
            $table->tinyInteger('number_of_games')->default(1);
            $table->text('message')->nullable();
            $table->dateTime('responded_at')->nullable();
            $table->foreignId('requester_team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('receiver_team_id')->constrained('teams')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scrim_requests');
    }
};
