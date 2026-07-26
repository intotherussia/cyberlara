<?php

namespace App\Console\Commands;

use App\Domains\Game\Models\Tournament;
use App\Domains\Game\Models\Team;
use App\Domains\Game\Models\MatchModel;
use App\Domains\Game\Enums\TournamentType;
use App\Domains\Game\Enums\MatchStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use function Laravel\Prompts{
    text,
    select,
    confirm,
    note,
    info,
    error,
    table,
    progress
};

class GameGenerateScheduleCommand extends Command
{
    protected $signature = 'game:generate-schedule {--days=7} {--tournament=}';
    protected $description = 'Генерация расписания турнира';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $tournamentId = $this->option('tournament');

        note(" ГЕНЕРАЦИЯ РАСПИСАНИЯ ТУРНИРА
");
2026-07-25 14:30:33
2026-07-25 14:30:33
2026-07-25 14:30:33
2026-07-25 14:30:33
2026-07-25 14:30:33
2026-07-25 14:30:33
2026-07-25 14:30:33
2026-07-25 14:30:33
2026-07-25 14:30:33
2026-07-25 14:30:33
2026-07-25 14:30:33
2026-07-25 14:30:33
2026-07-25 14:30:33
2026-07-25 14:30:33
2026-07-25 14:30:33
2026-07-25 14:30:33
2026-07-25 14:30:33
2026-07-25 14:30:33
2026-07-25 14:30:33
2026-07-25 14:30:33
2026-07-25 14:30:33
2026-07-25 14:30:33
2026-07-25 14:30:33
2026-07-25 14:30:33
2026-07-25 14:30:33
