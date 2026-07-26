<?php

namespace App\Console\Commands;

use App\Domains\Game\Models\MatchModel;
use App\Domains\Game\Services\MatchService;
use App\Domains\Game\Enums\MatchStatus;
use Illuminate\Console\Command;

use function Laravel\Prompts\{
    note,
    info,
    error,
    progress,
    confirm,
    table
};

class GameProcessMatchesCommand extends Command
{
    protected $signature = 'game:process-matches {--limit=10}';
    protected $description = 'Запуск ожидающих матчей';

    public function handle(MatchService $matchService): int
    {
        $limit = (int) $this->option('limit');

        note("⚡ ЗАПУСК ОЖИДАЮЩИХ МАТЧЕЙ\n");

        // Находим матчи для запуска
        $matches = MatchModel::where('status', MatchStatus::PENDING)
            ->where('started_at', '<=', now())
            ->orWhereNull('started_at')
            ->limit($limit)
            ->get();

        if ($matches->isEmpty()) {
            info("✅ Нет ожидающих матчей для запуска");
            return self::SUCCESS;
        }

        // Показываем список
        note(sprintf("📋 Найдено матчей для запуска: %d", $matches->count()));

        $tableData = [];
        foreach ($matches as $match) {
            $tableData[] = [
                $match->id,
                $match->team1?->name,
                $match->team2?->name,
                $match->started_at?->toDateTimeString() ?: 'Не установлено',
            ];
        }

        table(
            ['ID', 'Команда 1', 'Команда 2', 'Запланирован'],
            $tableData
        );

        if (!confirm("🚀 Запустить эти матчи?")) {
            info("❌ Запуск отменен");
            return self::FAILURE;
        }

        // Запускаем матчи
        progress(
            label: 'Запуск матчей',
            steps: $matches->count(),
            callback: function ($step) use ($matches, $matchService) {
                $match = $matches[$step];
                try {
                    $matchService->startMatch($match);
                    info("✅ Матч #{$match->id} запущен");
                } catch (\Exception $e) {
                    error("❌ Ошибка запуска матча #{$match->id}: " . $e->getMessage());
                }
            }
        );

        info("✅ Запуск матчей завершен!");
        return self::SUCCESS;
    }
}
