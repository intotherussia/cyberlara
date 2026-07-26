<?php

namespace App\Livewire;

use App\Domains\Game\Models\MatchModel;
use App\Domains\Game\Models\Team;
use App\Domains\Game\Models\Tournament;
use App\Domains\Game\Models\Player;
use App\Domains\Game\Enums\MatchStatus;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        // Ближайшие матчи (расписание)
        $upcomingMatches = MatchModel::with(['team1', 'team2'])
            ->where('status', MatchStatus::PENDING)
            ->where('started_at', '>=', now())
            ->orderBy('started_at')
            ->limit(10)
            ->get();

        // Последние завершенные матчи
        $recentMatches = MatchModel::with(['team1', 'team2', 'winner'])
            ->where('status', MatchStatus::FINISHED)
            ->orderBy('finished_at', 'desc')
            ->limit(5)
            ->get();

        // Топ-5 команд
        $topTeams = Team::with('players')
            ->orderBy('wins', 'desc')
            ->limit(5)
            ->get();

        // Топ-5 игроков
        $topPlayers = Player::with('team')
            ->orderBy('kills', 'desc')
            ->limit(5)
            ->get();

        // Глобальный чемпионат
        $globalTournament = Tournament::where('type', 'global')
            ->with(['matches' => function ($query) {
                $query->where('status', MatchStatus::FINISHED);
            }])
            ->first();

        return view('livewire.dashboard', [
            'upcomingMatches' => $upcomingMatches,
            'recentMatches' => $recentMatches,
            'topTeams' => $topTeams,
            'topPlayers' => $topPlayers,
            'globalTournament' => $globalTournament,
        ]);
    }
}
