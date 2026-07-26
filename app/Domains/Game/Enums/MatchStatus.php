<?php

namespace App\Domains\Game\Enums;

enum MatchStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case FINISHED = 'finished';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Ожидает начала',
            self::IN_PROGRESS => 'В процессе',
            self::FINISHED => 'Завершен',
            self::CANCELLED => 'Отменен',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::PENDING, self::IN_PROGRESS]);
    }
}