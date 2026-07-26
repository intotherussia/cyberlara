<?php

namespace App\Domains\Game\Jobs;

use App\Domains\Game\Models\MatchModel;
use App\Domains\Game\Services\SimulationService;
use App\Domains\Game\Events\MatchEventGenerated;
use App\Domains\Game\Jobs\FinalizeMatch;
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
        // Приводим к int (на всякий случай)
        $eventNumber = (int) $this->eventNumber;
        $totalEvents = (int) $this->totalEvents;

        Log::channel('game')->info("📊 Событие #{$eventNumber} из {$totalEvents}");
        Log::channel('game')->info("📊 eventNumber < totalEvents: " . ($eventNumber < $totalEvents ? 'true' : 'false'));

        $match = MatchModel::with(['team1', 'team2'])->findOrFail($this->matchId);

        if ($match->isFinished()) {
            Log::channel('game')->info("Матч {$this->matchId} уже завершен, пропускаем событие");
            return;
        }

        try {
            $event = $simulationService->generateEvent($match);
            event(new MatchEventGenerated($event));
            $this->updateProgress($match);

            Log::channel('game')->info("Матч {$this->matchId} - Событие #{$eventNumber} создано", [
                'kills' => $event->kills()->count(),
                'score' => "{$match->score_team1}:{$match->score_team2}",
                'progress' => round(($eventNumber / $totalEvents) * 100, 2) . '%',
            ]);

            if ($eventNumber < $totalEvents) {
                Log::channel('game')->info("📌 Событие #{$eventNumber} из {$totalEvents} - диспатчим следующее");
                ProcessMatchEvent::dispatch(
                    $this->matchId,
                    $eventNumber + 1,
                    $totalEvents
                )->delay(now()->addSeconds(10));
            } else {
                Log::channel('game')->info("🔥 ПОСЛЕДНЕЕ СОБЫТИЕ #{$eventNumber}! Завершаем матч синхронно");

                $simulationService->finalizeMatch($match);
                event(new \App\Domains\Game\Events\MatchFinished($match));
                Log::channel('game')->info("✅ Матч #{$this->matchId} завершен синхронно");
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
