<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_default_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->foreignId('team_member_id')->constrained('team_members')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['team_member_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_default_schedules');
    }
};
