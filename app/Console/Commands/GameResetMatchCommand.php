<?php

namespace App\Console\Commands;

use App\Domains\Game\Models\MatchModel;
use App\Domains\Game\Models\MatchEvent;
use App\Domains\Game\Models\MatchEventKill;
use App\Domains\Game\Enums\MatchStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

use function Laravel\Prompts\{
    text,
    confirm,
    note,
    info,
    error
};

class GameResetMatchCommand extends Command
{
    protected $signature = 'game:reset-match {id?}';
    protected $description = 'Полный сброс матча (включая события)';

    public function handle(): int
    {
        $id = $this->argument('id') ?? text(
            label: 'Введите ID матча для сброса',
            required: true,
            validate: function (string $value) {
                return MatchModel::where('id', $value)->exists() ? null : 'Матч не найден';
            }
        );

        $match = MatchModel::findOrFail($id);

        note("📋 МАТЧ #{$id}");
        note("Текущий статус: {$match->status->label()}");
        note("Текущий счет: {$match->score_team1} : {$match->score_team2}");

        if (!confirm("🔄 Полностью сбросить матч #{$id}?")) {
            info("❌ Сброс отменен");
            return self::FAILURE;
        }

        DB::transaction(function () use ($match) {
            // 1. Получаем ID всех событий
            $eventIds = $match->events()->pluck('id')->toArray();

            // 2. Удаляем убийства
            if (!empty($eventIds)) {
                MatchEventKill::whereIn('match_event_id', $eventIds)->delete();
                info("🗑️ Удалены убийства");
            }

            // 3. Удаляем события
            MatchEvent::whereIn('id', $eventIds)->delete();
            info("🗑️ Удалены события");

            // 4. Сбрасываем матч
            $match->status = MatchStatus::PENDING;
            $match->current_event = 0;
            $match->score_team1 = 0;
            $match->score_team2 = 0;
            $match->winner_team_id = null;
            $match->finished_at = null;
            $match->save();

            // 5. Очищаем Redis
            try {
                Redis::del(
                    "match:{$match->id}:progress",
                    "match:{$match->id}:score",
                    "match:{$match->id}:current_event",
                    "match:{$match->id}:total_events"
                );
                info("🗑️ Очищен Redis");
            } catch (\Exception $e) {
                // Redis не доступен - игнорируем
            }
        });

        info("✅ Матч #{$id} полностью сброшен!");
        note("📊 Статус: pending");
        note("📊 Счет: 0 : 0");

        return self::SUCCESS;
    }
}
