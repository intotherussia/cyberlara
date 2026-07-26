<?php

namespace App\Domains\Game\Services;

use App\Domains\Game\Models\Player;
use App\Domains\Game\Models\Map;
use App\Domains\Game\Enums\WeaponType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlayerGeneratorService
{
    /**
     * Создание одного игрока с рандомными характеристиками
     */
    public function generate(string $name, int $teamId): Player
    {
        return DB::transaction(function () use ($name, $teamId) {
            $stats = $this->generateStats();
            $favoriteMap = Map::inRandomOrder()->first();
            $favoriteWeapons = $this->generateFavoriteWeapons();

            $player = Player::create([
                'name' => $name,
                'team_id' => $teamId,
                'aim' => $stats['aim'],
                'skill' => $stats['skill'],
                'movement' => $stats['movement'],
                'favorite_map_id' => $favoriteMap?->id,
                'favorite_weapons' => $favoriteWeapons,
                'kills' => 0,
                'deaths' => 0,
                'matches_played' => 0,
            ]);

            Log::channel('game')->info("Создан игрок {$name}", [
                'team_id' => $teamId,
                'stats' => $stats,
                'favorite_map' => $favoriteMap?->name,
                'favorite_weapons' => $favoriteWeapons,
            ]);

            return $player;
        });
    }

    /**
     * Генерация характеристик: сумма ~3, одна характеристика выше
     */
    private function generateStats(): array
    {
        $stats = [
            'aim' => rand(80, 120) / 100,
            'skill' => rand(80, 120) / 100,
            'movement' => rand(80, 120) / 100,
        ];

        // Одна характеристика заметно лучше (+0.5-1.0)
        $primaryStat = array_rand($stats);
        $stats[$primaryStat] += rand(50, 100) / 100;

        foreach ($stats as $key => $value) {
            $stats[$key] = round($value, 2);
        }

        return $stats;
    }

    /**
     * Генерация 2 любимых оружий
     */
    private function generateFavoriteWeapons(): array
    {
        $weapons = WeaponType::cases();
        $first = $weapons[array_rand($weapons)];
        $remaining = array_filter($weapons, fn($w) => $w !== $first);
        $second = $remaining[array_rand($remaining)];
        
        return [$first->value, $second->value];
    }

    /**
     * Массовое создание игроков для команды
     */
    public function generateTeamPlayers(string $teamName, int $teamId, int $count = 6): array
    {
        $players = [];
        for ($i = 1; $i <= $count; $i++) {
            $players[] = $this->generate("{$teamName}_Player_{$i}", $teamId);
        }
        return $players;
    }
}