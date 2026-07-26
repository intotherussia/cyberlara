<?php

namespace App\Domains\Game\Enums;

enum WeaponType: string
{
    case RAIL = 'rail';
    case ROCKET = 'rocket';
    case SHAFT = 'shaft';
    case MG_PLASMA = 'mg_plasma';

    public function label(): string
    {
        return match($this) {
            self::RAIL => 'Railgun',
            self::ROCKET => 'Rocket Launcher',
            self::SHAFT => 'Lightning Gun (Shaft)',
            self::MG_PLASMA => 'Machinegun/Plasmagun',
        };
    }

    public function getDependentStat(): string
    {
        return match($this) {
            self::RAIL => 'aim',
            self::ROCKET => 'movement',
            self::SHAFT => 'skill',
            self::MG_PLASMA => 'movement',
        };
    }

    public function getSecondaryStat(): ?string
    {
        return match($this) {
            self::ROCKET => 'aim',
            default => null,
        };
    }

    public static function random(): self
    {
        return self::cases()[array_rand(self::cases())];
    }

    public static function randomExcluding(?self $exclude = null): self
    {
        $cases = self::cases();
        if ($exclude) {
            $cases = array_filter($cases, fn($case) => $case !== $exclude);
        }
        return $cases[array_rand($cases)];
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}