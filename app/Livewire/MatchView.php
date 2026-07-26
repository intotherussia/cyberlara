<?php

namespace App\Livewire;

use App\Domains\Game\Models\MatchModel;
use Illuminate\Support\Facades\Redis;
use Livewire\Component;

class MatchView extends Component
{
    public MatchModel $match;
    public array $events = [];
    public int $progress = 0;
    public string $score = '0 : 0';
    public int $currentEvent = 0;
    public int $totalEvents = 40;
    public bool $isFinished = false;
    public bool $isInProgress = false;

    protected $listeners = [
        'match.event' => 'handleNewEvent',
        'match.finished' => 'handleMatchFinished',
    ];

    public function mount(int $id): void
    {
        $this->match = MatchModel::with([
            'team1.players',
            'team2.players',
            'events.location',
            'events.kills.killer',
            'events.kills.killer.team',
            'events.kills.victim',
            'events.kills.victim.team',
            'map1',
            'map2',
            'winner',
        ])->findOrFail($id);

        $this->events = $this->formatEvents($this->match->events);
        $this->loadProgress();
        $this->updateStatus();
    }

    public function render()
    {
        return view('livewire.match-view', [
            'match' => $this->match,
        ]);
    }

    public function loadProgress(): void
    {
        try {
            $this->progress = (int) (Redis::get("match:{$this->match->id}:progress") ?? 0);
            $this->score = Redis::get("match:{$this->match->id}:score") ?? "{$this->match->score_team1} : {$this->match->score_team2}";
            $this->currentEvent = (int) (Redis::get("match:{$this->match->id}:current_event") ?? 0);
            $this->totalEvents = (int) (Redis::get("match:{$this->match->id}:total_events") ?? $this->match->total_events);
        } catch (\Exception $e) {
            $this->progress = $this->match->progress();
            $this->score = "{$this->match->score_team1} : {$this->match->score_team2}";
            $this->currentEvent = $this->match->current_event;
            $this->totalEvents = $this->match->total_events;
        }
    }

    public function updateStatus(): void
    {
        $this->isFinished = $this->match->isFinished();
        $this->isInProgress = $this->match->isInProgress();
    }

    public function handleNewEvent(array $data): void
    {
        $this->progress = $data['progress'] ?? 0;
        $this->score = "{$data['score']['team1']} : {$data['score']['team2']}";
        $this->currentEvent = $data['event_number'] ?? 0;

        $location = $data['location'] ?? 'Неизвестно';
        if (!is_string($location)) {
            $location = 'Неизвестно';
        }

        $kills = $data['kills'] ?? [];
        if (!is_array($kills)) {
            $kills = [];
        }

        $formattedKills = [];
        foreach ($kills as $kill) {
            $formattedKills[] = [
                'killer' => $kill['killer'] ?? '???',
                'killer_team_id' => $kill['killer_team_id'] ?? null,
                'killer_team_name' => $kill['killer_team_name'] ?? '???',
                'victim' => $kill['victim'] ?? '???',
                'victim_team_id' => $kill['victim_team_id'] ?? null,
                'victim_team_name' => $kill['victim_team_name'] ?? '???',
                'weapon' => $kill['weapon'] ?? '?',
            ];
        }

        array_unshift($this->events, [
            'event_number' => $data['event_number'] ?? 0,
            'location' => $location,
            'kills' => $formattedKills,
            'kills_count' => $data['kills_count'] ?? 0,
        ]);

        $this->match->score_team1 = $data['score']['team1'];
        $this->match->score_team2 = $data['score']['team2'];
    }

    public function handleMatchFinished(array $data): void
    {
        $this->isFinished = true;
        $this->isInProgress = false;
        $this->progress = 100;

        $this->match->refresh();
        $this->match->load(['winner', 'team1', 'team2']);

        $this->dispatch('match-finished', [
            'winner' => $data['winner'] ?? null,
        ]);
    }

    public function refreshMatch(): void
    {
        // Полностью перезагружаем матч с ПРАВИЛЬНЫМИ связями
        $this->match = MatchModel::with([
            'team1.players',           // ← Игроки команды 1
            'team2.players',           // ← Игроки команды 2
            'events.location',
            'events.kills.killer',     // ← Убийца
            'events.kills.killer.team', // ← Команда убийцы!
            'events.kills.victim',     // ← Жертва
            'events.kills.victim.team', // ← Команда жертвы!
            'map1',
            'map2',
            'winner',
        ])->findOrFail($this->match->id);

        $this->loadProgress();
        $this->updateStatus();

        // Форматируем события с информацией о командах
        $this->events = $this->match->events->map(function ($event) {
            $kills = [];
            foreach ($event->kills as $kill) {
                $kills[] = [
                    'killer' => $kill->killer?->name ?? '???',
                    'killer_team_id' => $kill->killer?->team_id ?? null,
                    'killer_team_name' => $kill->killer?->team?->name ?? '???',
                    'victim' => $kill->victim?->name ?? '???',
                    'victim_team_id' => $kill->victim?->team_id ?? null,
                    'victim_team_name' => $kill->victim?->team?->name ?? '???',
                    'weapon' => $kill->weapon_used ?? '?',
                ];
            }

            return [
                'event_number' => $event->event_number,
                'location' => $event->location?->name ?? 'Неизвестно',
                'kills' => $kills,
                'kills_count' => count($kills),
            ];
        })->toArray();
    }

    /**
     * Форматирование событий с именами игроков
     */
    private function formatEvents($events): array
    {
        return $events->map(function ($event) {
            $kills = [];
            foreach ($event->kills as $kill) {
                $kills[] = [
                    'killer' => $kill->killer?->name ?? '???',
                    'killer_team_id' => $kill->killer?->team_id ?? null,
                    'killer_team_name' => $kill->killer?->team?->name ?? '???',
                    'victim' => $kill->victim?->name ?? '???',
                    'victim_team_id' => $kill->victim?->team_id ?? null,
                    'victim_team_name' => $kill->victim?->team?->name ?? '???',
                    'weapon' => $kill->weapon_used ?? '?',
                ];
            }

            return [
                'event_number' => $event->event_number,
                'location' => $event->location?->name ?? 'Неизвестно',
                'kills' => $kills,
                'kills_count' => count($kills),
            ];
        })->toArray();
    }
}
