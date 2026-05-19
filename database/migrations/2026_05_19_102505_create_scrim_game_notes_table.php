<?php

use App\Enums\TypeScrimGameNote;
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
        Schema::create('scrim_game_notes', function (Blueprint $table) {
            $table->id();
            $table->enum('type', TypeScrimGameNote::cases());
            $table->text('note');
            $table->foreignId('scrim_game_id')->constrained('scrim_games')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scrim_game_notes');
    }
};
