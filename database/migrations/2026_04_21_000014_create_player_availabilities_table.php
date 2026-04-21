<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->date('date');
            $table->enum('status', ['available', 'unavailable', 'maybe']);
            $table->enum('type', ['medical', 'exam', 'vacation', 'other'])->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('note', 100)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'team_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_availabilities');
    }
};
