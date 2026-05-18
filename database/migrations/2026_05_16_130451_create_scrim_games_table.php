<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scrim_games', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->unsignedTinyInteger('game_number');
            $table->foreignId('winner_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->integer('duration_minutes')->nullable();
            $table->string('screenshot')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('scrim_id')->constrained('scrims')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['scrim_id', 'game_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scrim_games');
    }
};
