<?php

namespace App\Domains\Game\Services;

use App\Domains\Game\Models\MatchModel;
use App\Domains\Game\Models\MatchEvent;
use App\Domains\Game\Models\Player;
use App\Domains\Game\Models\Location;
use App\Domains\Game\Models\Map;
use App\Domains\Game\Enums\WeaponType;
use App\Domains\Game\Enums\MatchStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SimulationService
{
    private const MAX_PLAYERS_PER_EVENT = 3;
    private const MIN_PLAYERS_PER_EVENT = 1;
    private const PROGRESSION_INCREMENT = 0.01;

    /**
     * Генерация одного игрового события
     */
    public function generateEvent(MatchModel $match): ?MatchEvent
    {
        return DB::transaction(function () use ($match) {
            // Проверяем, закончились ли события на текущей карте
            if ($match->current_event > 0 && $match->isCurrentMapFinished()) {
                if ($match->current_map_index === 0) {
                    $match->switchToNextMap();
                    Log::channel('game')->info("🔄 Переход на вторую карту: {$match->currentMap()?->display_name}");

                    // После смены карты продолжаем генерацию события на новой карте
                } else {
                    // Обе карты сыграны, матч завершен
                    Log::channel('game')->info("⏹️ Обе карты сыграны, матч завершен");
                    return null;
                }
            }

            $currentMap = $match->currentMap();
            if (!$currentMap) {
                throw new \RuntimeException('Current map not found');
            }

            $location = $currentMap->locations()->inRandomOrder()->first();
            if (!$location) {
                throw new \RuntimeException('No locations found on current map');
            }

            $team1Players = $this->getRandomPlayers($match->team1_id, $match);
            $team2Players = $this->getRandomPlayers($match->team2_id, $match);

            $eventNumber = $match->current_event + 1;

            $event = MatchEvent::create([
                'match_id' => $match->id,
                'location_id' => $location->id,
                'team1_player_ids' => $team1Players->pluck('id')->toArray(),
                'team2_player_ids' => $team2Players->pluck('id')->toArray(),
                'event_number' => $eventNumber,
                'occurred_at' => now(),
            ]);

            $kills = $this->simulateFights($team1Players, $team2Players, $location, $currentMap);

            foreach ($kills as $kill) {
                $event->kills()->create([
                    'killer_player_id' => $kill['killer']->id,
                    'victim_player_id' => $kill['victim']->id,
                    'weapon_used' => $location->priority_weapon,
                ]);

                if ($kill['killer']->team_id === $match->team1_id) {
                    $match->increment('score_team1');
                } else {
                    $match->increment('score_team2');
                }
            }

            $match->current_event = $eventNumber;
            $match->save();

            $this->applyProgression($event, $location, $kills);

            Log::channel('game')->info("Событие #{$eventNumber} в матче {$match->id}", [
                'kills' => count($kills),
                'location' => $location->name,
                'score' => "{$match->score_team1}:{$match->score_team2}",
                'current_map' => $currentMap->display_name,
                'map_index' => $match->current_map_index,
            ]);

            return $event;
        });
    }

    /**
     * Получение случайных игроков из команды
     */
    private function getRandomPlayers(int $teamId, MatchModel $match): \Illuminate\Support\Collection
    {
        $playerIds = $match->getTeamPlayers($teamId);

        if (empty($playerIds)) {
            return collect();
        }

        $count = rand(self::MIN_PLAYERS_PER_EVENT, self::MAX_PLAYERS_PER_EVENT);
        $count = min($count, count($playerIds));

        $selectedIds = collect($playerIds)->random($count);

        return Player::whereIn('id', $selectedIds)->get();
    }

    /**
     * Симуляция боёв между игроками
     */
    private function simulateFights(
        $team1Players,
        $team2Players,
        Location $location,
        Map $map
    ): array {
        $kills = [];
        $weapon = WeaponType::from($location->priority_weapon);

        foreach ($team1Players as $attacker) {
            foreach ($team2Players as $defender) {
                if ($this->calculateFightOutcome($attacker, $defender, $weapon, $map)) {
                    $kills[] = ['killer' => $attacker, 'victim' => $defender];
                }
            }
        }

        foreach ($team2Players as $attacker) {
            foreach ($team1Players as $defender) {
                if ($this->calculateFightOutcome($attacker, $defender, $weapon, $map)) {
                    $kills[] = ['killer' => $attacker, 'victim' => $defender];
                }
            }
        }

        return $kills;
    }

    /**
     * Расчет исхода боя
     */
    private function calculateFightOutcome(
        Player $attacker,
        Player $defender,
        WeaponType $weapon,
        Map $map
    ): bool {
        $attackStat = $attacker->getEffectiveStat($weapon, $map);
        $defenseStat = $defender->getEffectiveStat($weapon, $map);

        $baseChance = $attackStat / ($attackStat + $defenseStat + 0.1);
        $randomFactor = rand(80, 120) / 100;
        $finalChance = min(max($baseChance * $randomFactor, 0.1), 0.9);

        return (mt_rand(1, 1000) / 1000) <= $finalChance;
    }

    /**
     * Прокачка игроков после события
     */
    private function applyProgression(MatchEvent $event, Location $location, array $kills): void
    {
        $allPlayerIds = array_merge(
            (array) $event->team1_player_ids,
            (array) $event->team2_player_ids
        );

        $weapon = WeaponType::from($location->priority_weapon);
        $statName = $weapon->getDependentStat();

        foreach ($allPlayerIds as $playerId) {
            $player = Player::find($playerId);
            if (!$player) {
                continue;
            }

            $weaponStat = $player->weaponStats()->firstOrCreate([
                'weapon_type' => $weapon->value,
            ]);
            $weaponStat->increment('uses_count');

            $hasKill = collect($kills)->contains(fn($kill) => $kill['killer']->id === $playerId);

            if ($hasKill) {
                $weaponStat->increment('kills_with_weapon');
                $weaponStat->increment('efficiency', 0.05);
                $player->applyProgression($statName, self::PROGRESSION_INCREMENT);

                if ($secondaryStat = $weapon->getSecondaryStat()) {
                    $player->applyProgression($secondaryStat, self::PROGRESSION_INCREMENT * 0.5);
                }
            }

            $player->applyProgression($statName, self::PROGRESSION_INCREMENT * 0.2);
        }
    }

    /**
     * Завершение матча
     */
    public function finalizeMatch(MatchModel $match): void
    {
        // Проверяем, не завершен ли уже матч
        if ($match->isFinished()) {
            Log::channel('game')->info("Матч #{$match->id} уже завершен, пропускаем финализацию");
            return;
        }

        DB::transaction(function () use ($match) {
            if ($match->score_team1 > $match->score_team2) {
                $match->winner_team_id = $match->team1_id;
            } elseif ($match->score_team2 > $match->score_team1) {
                $match->winner_team_id = $match->team2_id;
            }

            $match->status = MatchStatus::FINISHED;
            $match->finished_at = now();
            $match->save();

            Log::channel('game')->info("📊 Матч #{$match->id} финализирован в БД");

            $this->updateTeamStats($match);
            $this->updatePlayerStats($match);

            Log::channel('game')->info("Матч {$match->id} завершен", [
                'winner' => $match->winner?->name,
                'score' => "{$match->score_team1}:{$match->score_team2}",
            ]);
        });
    }

    /**
     * Обновление статистики команд
     */
    private function updateTeamStats(MatchModel $match): void
    {
        $team1 = $match->team1;
        $team2 = $match->team2;

        $team1->increment('total_matches');
        $team2->increment('total_matches');

        if ($match->winner_team_id === $team1->id) {
            $team1->increment('wins');
            $team2->increment('losses');
        } elseif ($match->winner_team_id === $team2->id) {
            $team2->increment('wins');
            $team1->increment('losses');
        }
    }

    /**
     * Обновление статистики игроков
     */
    private function updatePlayerStats(MatchModel $match): void
    {
        $allPlayerIds = array_merge(
            $match->getTeamPlayers($match->team1_id),
            $match->getTeamPlayers($match->team2_id)
        );

        foreach ($allPlayerIds as $playerId) {
            $player = Player::find($playerId);
            if (!$player) {
                continue;
            }

            $kills = $match->kills()->where('killer_player_id', $playerId)->count();
            $deaths = $match->kills()->where('victim_player_id', $playerId)->count();

            $player->increment('kills', $kills);
            $player->increment('deaths', $deaths);
            $player->increment('matches_played');

            // Используем firstOrCreate вместо create
            $player->matchStats()->firstOrCreate(
                [
                    'match_id' => $match->id,
                    'team_id' => $player->team_id,
                ],
                [
                    'kills' => $kills,
                    'deaths' => $deaths,
                    'aim_before' => $player->getOriginal('aim'),
                    'skill_before' => $player->getOriginal('skill'),
                    'movement_before' => $player->getOriginal('movement'),
                    'aim_after' => $player->aim,
                    'skill_after' => $player->skill,
                    'movement_after' => $player->movement,
                    'weapons_used' => $player->weaponStats()->pluck('weapon_type')->toArray(),
                ]
            );
        }
    }
}
