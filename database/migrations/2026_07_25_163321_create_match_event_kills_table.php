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
        Schema::create('match_event_kills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('killer_player_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('victim_player_id')->constrained('players')->cascadeOnDelete();
            $table->string('weapon_used'); // rail, rocket, shaft, mg_plasma
            $table->timestamps();
            
            $table->index(['killer_player_id', 'victim_player_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_event_kills');
    }
};
