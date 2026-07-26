<?php

namespace App\Domains\Game\Jobs;

use App\Domains\Game\Models\MatchModel;
use App\Domains\Game\Services\SimulationService;
use App\Domains\Game\Events\MatchFinished;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class FinalizeMatch implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = [10, 30, 60];

    public function __construct(
        private readonly int $matchId
    ) {}

    public function handle(SimulationService $simulationService): void
    {
        Log::channel('game')->info("🚀 FinalizeMatch ВЫЗВАН для матча #{$this->matchId}");

        try {
            $match = MatchModel::with(['team1', 'team2', 'winner'])->findOrFail($this->matchId);

            Log::channel('game')->info("📊 Текущий счет матча #{$this->matchId}: {$match->score_team1} : {$match->score_team2}");

            // Финализируем матч
            $simulationService->finalizeMatch($match);

            // Диспатчим событие о завершении
            event(new MatchFinished($match));

            // Очищаем Redis (если работает)
            $this->clearRedisKeys();

            Log::channel('game')->info("✅ Матч #{$this->matchId} успешно завершен", [
                'winner' => $match->winner?->name,
                'score' => "{$match->score_team1}:{$match->score_team2}",
                'team1' => $match->team1?->name,
                'team2' => $match->team2?->name,
            ]);

        } catch (\Exception $e) {
            Log::channel('game')->error("❌ Ошибка при завершении матча #{$this->matchId}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Очистка Redis ключей (без ошибок, если Redis не доступен)
     */
    private function clearRedisKeys(): void
    {
        try {
            Redis::del("match:{$this->matchId}:progress");
            Redis::del("match:{$this->matchId}:score");
            Redis::del("match:{$this->matchId}:current_event");
            Redis::del("match:{$this->matchId}:total_events");
            Log::channel('game')->info("🗑️ Redis ключи для матча #{$this->matchId} очищены");
        } catch (\Exception $e) {
            // Redis не доступен - просто логируем и продолжаем
            Log::channel('game')->warning("⚠️ Redis недоступен при очистке: " . $e->getMessage());
        }
    }
}
