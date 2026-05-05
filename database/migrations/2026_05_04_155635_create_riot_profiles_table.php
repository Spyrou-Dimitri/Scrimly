<?php

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
        Schema::create('riot_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('riot_tag', 50)->nullable();
            $table->string('riot_puuid', 100)->nullable();
            $table->string('tier')->nullable();
            $table->string('rank')->nullable();
            $table->integer('lp')->nullable();
            $table->integer('wins')->default(0);
            $table->integer('losses')->default(0);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riot_profiles');
    }
};
