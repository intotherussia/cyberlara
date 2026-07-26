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
        Schema::create('player_weapon_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->string('weapon_type'); // rail, rocket, shaft, mg_plasma
            $table->integer('uses_count')->default(0);
            $table->integer('kills_with_weapon')->default(0);
            $table->decimal('efficiency', 5, 2)->default(0);
            $table->timestamps();
            
            $table->unique(['player_id', 'weapon_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_weapon_stats');
    }
};
