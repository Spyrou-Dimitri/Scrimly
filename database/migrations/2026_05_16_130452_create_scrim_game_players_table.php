<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scrim_game_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scrim_game_id')->constrained('scrim_games')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('champion', 50)->nullable();
            $table->integer('kills')->default(0);
            $table->integer('deaths')->default(0);
            $table->integer('assists')->default(0);
            $table->integer('cs')->default(0);
            $table->integer('damage_dealt')->nullable();
            $table->integer('vision_score')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scrim_game_players');
    }
};
