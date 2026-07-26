<?php

namespace Database\Seeders;

use App\Domains\Game\Services\PlayerGeneratorService;
use App\Domains\Game\Models\Team;
use Illuminate\Database\Seeder;

class DemoTeamSeeder extends Seeder
{
    public function run(PlayerGeneratorService $generator): void
    {
        // Команда 1
        $team1 = Team::create([
            'name' => 'Team Alpha',
            'tag' => 'ALPHA',
            'owner_id' => 1, // предполагаем, что пользователь с id=1 существует
            'wins' => 0,
            'losses' => 0,
            'total_matches' => 0,
        ]);

        $generator->generateTeamPlayers('Alpha', $team1->id, 6);

        // Команда 2
        $team2 = Team::create([
            'name' => 'Team Beta',
            'tag' => 'BETA',
            'owner_id' => 1,
            'wins' => 0,
            'losses' => 0,
            'total_matches' => 0,
        ]);

        $generator->generateTeamPlayers('Beta', $team2->id, 6);
    }
}