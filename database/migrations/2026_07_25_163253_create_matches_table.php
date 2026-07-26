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
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team1_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('team2_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('tournament_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('pending'); // pending, in_progress, finished, cancelled
            $table->foreignId('map1_id')->constrained('maps');
            $table->foreignId('map2_id')->constrained('maps');
            $table->integer('current_map_index')->default(0);
            $table->integer('score_team1')->default(0);
            $table->integer('score_team2')->default(0);
            $table->foreignId('winner_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->integer('total_events')->default(40);
            $table->integer('current_event')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'started_at']);
            $table->index('tournament_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
