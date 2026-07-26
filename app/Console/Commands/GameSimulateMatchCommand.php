<?php

namespace App\Console\Commands;

use App\Domains\Game\Models\MatchModel;
use App\Domains\Game\Services\MatchService;
use App\Domains\Game\Events\MatchEventGenerated;
use App\Domains\Game\Events\MatchFinished;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\DB;

use function Laravel\Prompts\{
    text,
    select,
    confirm,
    note,
    info,
    error,
    table,
    progress
};

class GameSimulateMatchCommand extends Command
{
    protected $signature = 'game:simulate-match {matchId?}';
    protected $description = 'Симуляция матча между командами';

    public function handle(MatchService $matchService): int
    {
        // Получаем ID матча
        $matchId = $this->argument('matchId') ?? text(
            label: 'Введите ID матча для симуляции',
            required: true,
            validate: function (string $value) {
                return MatchModel::where('id', $value)->exists() ? null : 'Матч с таким ID не найден';
            }
        );

        $match = MatchModel::with(['team1', 'team2', 'map1', 'map2'])->findOrFail($matchId);

        // Проверяем статус
        if ($match->isFinished()) {
            error("Матч #{$match->id} уже завершен!");
            return self::FAILURE;
        }

        if ($match->isInProgress()) {
            error("Матч #{$match->id} уже в процессе!");
            return self::FAILURE;
        }

        // Показываем информацию о матче
        note(sprintf(
            " МАТЧ #%d\n\n" .
            "Команда 1: %s\n" .
            "Команда 2: %s\n" .
            "Карта 1: %s\n" .
            "Карта 2: %s\n" .
            "Событий: %d\n",
            $match->id,
            $match->team1->name,
            $match->team2->name,
            $match->map1->display_name,
            $match->map2->display_name,
            $match->total_events
        ));

        // Подтверждение
        if (!confirm(" Запустить симуляцию матча #{$match->id}?")) {
            info(" Симуляция отменена");
            return self::FAILURE;
        }

        // Запускаем матч
        $matchService->startMatch($match);

        // Диспатчим событие
        //Event::dispatch(new MatchEventGenerated($match));

        info(" Матч #{$match->id} запущен!");
        info(" Следите за прогрессом:");
        info("   redis-cli GET match:{$match->id}:progress");
        info("   redis-cli GET match:{$match->id}:score");
        info("   redis-cli GET match:{$match->id}:current_event");

        return self::SUCCESS;
    }
}
