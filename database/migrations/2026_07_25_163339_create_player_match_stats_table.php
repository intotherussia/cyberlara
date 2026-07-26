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
        Schema::create('player_match_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('match_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained();
            $table->integer('kills')->default(0);
            $table->integer('deaths')->default(0);
            $table->decimal('aim_before', 4, 2);
            $table->decimal('skill_before', 4, 2);
            $table->decimal('movement_before', 4, 2);
            $table->decimal('aim_after', 4, 2);
            $table->decimal('skill_after', 4, 2);
            $table->decimal('movement_after', 4, 2);
            $table->json('weapons_used');
            $table->timestamps();
            
            $table->unique(['player_id', 'match_id']);
            $table->index(['match_id', 'team_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_match_stats');
    }
};
