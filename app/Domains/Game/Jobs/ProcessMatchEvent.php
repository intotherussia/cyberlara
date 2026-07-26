<?php

namespace App\Domains\Game\Jobs;

use App\Domains\Game\Models\MatchModel;
use App\Domains\Game\Services\SimulationService;
use App\Domains\Game\Events\MatchEventGenerated;
use App\Domains\Game\Events\MatchFinished;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ProcessMatchEvent implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = [10, 30, 60];

    public function __construct(
        private readonly int $matchId,
        private readonly int $eventNumber,
        private readonly int $totalEvents
    ) {
        $this->onQueue('game-events');
    }

    public function handle(SimulationService $simulationService): void
    {
        $eventNumber = (int) $this->eventNumber;
        $totalEvents = (int) $this->totalEvents;

        Log::channel('game')->info("📊 Событие #{$eventNumber} из {$totalEvents}");

        $match = MatchModel::with(['team1', 'team2', 'map1', 'map2'])->findOrFail($this->matchId);

        if ($match->isFinished()) {
            Log::channel('game')->info("Матч {$this->matchId} уже завершен, пропускаем событие");
            return;
        }

        try {
            // Генерируем событие
            $event = $simulationService->generateEvent($match);

            // Если событие null — матч завершен (обе карты сыграны)
            if ($event === null) {
                Log::channel('game')->info("⏹️ Матч #{$this->matchId} завершен (обе карты сыграны)");
                $simulationService->finalizeMatch($match);
                event(new MatchFinished($match));
                Log::channel('game')->info("✅ Матч #{$this->matchId} завершен");
                return;
            }

            // Диспатчим событие
            event(new MatchEventGenerated($event));
            $this->updateProgress($match);

            Log::channel('game')->info("Матч {$this->matchId} - Событие #{$eventNumber} создано", [
                'kills' => $event->kills()->count(),
                'score' => "{$match->score_team1}:{$match->score_team2}",
                'progress' => round(($eventNumber / $totalEvents) * 100, 2) . '%',
            ]);

            // Если не последнее событие — диспатчим следующее
            if ($eventNumber < $totalEvents) {
                Log::channel('game')->info("📌 Событие #{$eventNumber} из {$totalEvents} - диспатчим следующее");
                ProcessMatchEvent::dispatch(
                    $this->matchId,
                    $eventNumber + 1,
                    $totalEvents
                )->delay(now()->addSeconds(10));
            } else {
                Log::channel('game')->info("🔥 ПОСЛЕДНЕЕ СОБЫТИЕ #{$eventNumber}! Завершаем матч #{$this->matchId}");
                $simulationService->finalizeMatch($match);
                event(new MatchFinished($match));
                Log::channel('game')->info("✅ Матч #{$this->matchId} завершен");
            }
        } catch (\Exception $e) {
            Log::channel('game')->error("Ошибка в матче {$this->matchId} событие {$eventNumber}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    private function updateProgress(MatchModel $match): void
    {
        try {
            $progress = round(($this->eventNumber / $this->totalEvents) * 100, 2);
            Redis::set("match:{$this->matchId}:progress", $progress);
            Redis::set("match:{$this->matchId}:score", "{$match->score_team1}:{$match->score_team2}");
            Redis::set("match:{$this->matchId}:current_event", $this->eventNumber);
            Redis::set("match:{$this->matchId}:total_events", $this->totalEvents);
        } catch (\Exception $e) {
            Log::channel('game')->warning("Redis недоступен: " . $e->getMessage());
        }
    }
}
