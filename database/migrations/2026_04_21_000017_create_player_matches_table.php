<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('match_id', 50);
            $table->string('champion', 50);
            $table->enum('result', ['victory', 'defeat']);
            $table->integer('kills');
            $table->integer('deaths');
            $table->integer('assists');
            $table->integer('cs');
            $table->integer('damage_dealt')->nullable();
            $table->integer('vision_score')->nullable();
            $table->integer('duration_seconds');
            $table->timestamp('played_at');
            $table->timestamp('synced_at');
            $table->timestamps();

            $table->unique(['user_id', 'match_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_matches');
    }
};
