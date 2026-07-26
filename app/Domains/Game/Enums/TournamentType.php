<?php

namespace App\Domains\Game\Enums;

enum TournamentType: string
{
    case GLOBAL = 'global';
    case CUSTOM = 'custom';

    public function label(): string
    {
        return match($this) {
            self::GLOBAL => 'Глобальный чемпионат',
            self::CUSTOM => 'Пользовательский турнир',
        };
    }
}