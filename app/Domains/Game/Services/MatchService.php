<?php

namespace App\Domains\Game\Services;

use App\Domains\Game\Models\MatchModel;
use App\Domains\Game\Jobs\ProcessMatchEvent;
use App\Domains\Game\Enums\MatchStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MatchService
{
    private const DEFAULT_TOTAL_EVENTS = 5;

    /**
     * Создание товарищеского матча
     */
    public function createFriendlyMatch(int $team1Id, int $team2Id, int $totalEvents = self::DEFAULT_TOTAL_EVENTS): MatchModel
    {
        return DB::transaction(function () use ($team1Id, $team2Id, $totalEvents) {
            $maps = Map::where('is_active', true)
                ->inRandomOrder()
                ->limit(2)
                ->get();

            if ($maps->count() < 2) {
                throw new \RuntimeException('Недостаточно активных карт для матча');
            }

            $match = MatchModel::create([
                'team1_id' => $team1Id,
                'team2_id' => $team2Id,
                'map1_id' => $maps[0]->id,
                'map2_id' => $maps[1]->id,
                'total_events' => $totalEvents,
                'status' => MatchStatus::PENDING,
                'current_event' => 0,
                'score_team1' => 0,
                'score_team2' => 0,
            ]);

            Log::channel('game')->info("Создан товарищеский матч #{$match->id}", [
                'team1' => $team1Id,
                'team2' => $team2Id,
                'map1' => $maps[0]->name,
                'map2' => $maps[1]->name,
            ]);

            return $match;
        });
    }

    /**
     * Запуск матча
     */
    public function startMatch(MatchModel $match): void
    {
        if ($match->isInProgress()) {
            throw new \RuntimeException('Матч уже в процессе');
        }

        if ($match->isFinished()) {
            throw new \RuntimeException('Матч уже завершен');
        }

        $match->update([
            'status' => MatchStatus::IN_PROGRESS,
            'started_at' => now(),
        ]);

        Log::channel('game')->info("Матч #{$match->id} запущен");

        ProcessMatchEvent::dispatch($match->id, 1, $match->total_events);
    }
}
