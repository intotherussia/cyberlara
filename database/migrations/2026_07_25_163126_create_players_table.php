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
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('aim', 4, 2)->default(1.0);
            $table->decimal('skill', 4, 2)->default(1.0);
            $table->decimal('movement', 4, 2)->default(1.0);
            $table->foreignId('favorite_map_id')->nullable()->constrained('maps')->nullOnDelete();
            $table->json('favorite_weapons')->nullable();
            $table->integer('kills')->default(0);
            $table->integer('deaths')->default(0);
            $table->integer('matches_played')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
