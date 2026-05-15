<?php

use App\Enums\Language;
use App\Enums\LolGoal;
use App\Enums\LolServeur;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30)->unique();
            $table->string('slug', 30)->unique();
            $table->string('code')->unique();
            $table->string('tag', 4)->unique();
            $table->string('logo_type')->default('default');
            $table->string('logo_value')->default('Demacia');
            $table->text('description', 1000)->nullable();
            $table->integer('starter_average_elo')->nullable();
            $table->enum('language', Language::cases());
            $table->enum('server', LolServeur::cases());
            $table->enum('goal', LolGoal::cases());
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
