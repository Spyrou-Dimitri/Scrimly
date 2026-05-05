<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riot_matches', function (Blueprint $table) {
            $table->id();
            $table->string('match_id');
            $table->integer('game_duration');
            $table->timestamp('played_at');
            $table->string('champion_name');
            $table->integer('champion_id');
            $table->integer('champion_level');
            $table->string('role', 20)->nullable();
            $table->boolean('win');
            $table->integer('kills');
            $table->integer('deaths');
            $table->integer('assists');
            $table->integer('cs');
            $table->json('items')->nullable();
            $table->foreignId('riot_profile_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['riot_profile_id', 'match_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riot_matches');
    }
};