<?php

namespace Database\Seeders;

use App\Domains\Game\Models\Tournament;
use App\Domains\Game\Models\MatchModel;
use App\Domains\Game\Models\Team;
use App\Domains\Game\Models\Map;
use App\Domains\Game\Enums\TournamentType;
use App\Domains\Game\Enums\MatchStatus;
use Illuminate\Database\Seeder;

class GlobalTournamentSeeder extends Seeder
{
    public function run(): void
    {
        // Создаем глобальный турнир
        $tournament = Tournament::create([
            'name' => 'Глобальный чемпионат Quake 3',
            'type' => TournamentType::GLOBAL,
            'rules' => [
                'format' => 'tdm',
                'maps' => 2,
                'max_teams' => 8,
            ],
            'is_active' => true,
            'started_at' => now(),
        ]);

        // Получаем команды
        $teams = Team::all();
        if ($teams->count() < 2) {
            return;
        }

        // Получаем карты
        $maps = Map::where('is_active', true)->get();
        if ($maps->count() < 2) {
            return;
        }

        // Создаем матчи между парами команд
        $teamPairs = $teams->shuffle();
        for ($i = 0; $i < $teamPairs->count() - 1; $i += 2) {
            if ($i + 1 >= $teamPairs->count()) break;

            $team1 = $teamPairs[$i];
            $team2 = $teamPairs[$i + 1];
            $selectedMaps = $maps->random(2);

            MatchModel::create([
                'team1_id' => $team1->id,
                'team2_id' => $team2->id,
                'tournament_id' => $tournament->id,
                'status' => MatchStatus::PENDING,
                'map1_id' => $selectedMaps[0]->id,
                'map2_id' => $selectedMaps[1]->id,
                'total_events' => 40,
                'current_event' => 0,
                'score_team1' => 0,
                'score_team2' => 0,
                'started_at' => now()->addDays(rand(1, 7)),
            ]);
        }
    }
}