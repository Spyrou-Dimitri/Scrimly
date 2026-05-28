<?php

use App\Enums\RoleInGame;
use App\Enums\RoleInTeam;
use App\Enums\StatusInvitation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_invitations', function (Blueprint $table) {
            $table->id();
            $table->enum('status', StatusInvitation::cases());
            $table->enum('roleInTeam', RoleInTeam::cases());
            $table->enum('roleInGame', RoleInGame::cases())->nullable();
            $table->text('motivation')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_invitations');
    }
};
