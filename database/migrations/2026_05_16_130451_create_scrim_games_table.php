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
            $table->boolean('is_victory')->nullable();
            $table->integer('duration')->nullable();
            $table->text('notes')->nullable();
            $table->longText('opponent_team_members_starters')->nullable();
            $table->foreignId('scrim_id')->constrained('scrims')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scrim_games');
    }
};
