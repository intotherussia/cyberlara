<?php

namespace App\Livewire\Components;

use App\Domains\Game\Models\Map;
use App\Domains\Game\Models\MatchModel;
use App\Domains\Game\Models\Team;
use App\Domains\Game\Services\MatchService;
use App\Domains\Game\Events\MatchEventGenerated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Livewire\Component;
use Livewire\WithPagination;

class MatchControl extends Component
{
    use WithPagination;

    public $selectedTournament = null;
    public $team1;
    public $team2;
    public $map1;
    public $map2;
    public $totalEvents = 40;
    public $isModalOpen = false;
    public $search = '';

    protected $rules = [
        'selectedTournament' => 'nullable|exists:tournaments,id',
        'team1' => 'required|distinct|exists:teams,id',
        'team2' => 'required|distinct|exists:teams,id',
        'map1' => 'required|distinct|exists:maps,id',
        'map2' => 'required|distinct|exists:maps,id',
        'totalEvents' => 'required|integer|min:4|max:100',
    ];

    public function render()
    {
        $matches = MatchModel::with(['team1', 'team2', 'winner'])
            ->when($this->search, function ($query) {
                $query->whereHas('team1', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                })
                    ->orWhereHas('team2', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $teams = Team::orderBy('name')->get();
        $maps = Map::orderBy('display_name')->get();

        return view('livewire.match-control', [
            'matches' => $matches,
            'teams' => $teams,
            'maps' => $maps
        ]);
    }

    public function openModal()
    {
        $this->reset(['team1', 'team2', 'map1', 'map2', 'totalEvents', 'isModalOpen']);
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
    }

    public function startMatch(MatchService $matchService)
    {
        $this->validate();

        Log::channel('game')->info('Создание матча', [
            'team1' => $this->team1,
            'team2' => $this->team2,
            'totalEvents' => $this->totalEvents,
        ]);

        try {
            DB::beginTransaction();

            $match = $matchService->createFriendlyMatch(
                $this->team1,
                $this->team2,
                $this->totalEvents ?? 40
            );

            $matchService->startMatch($match);

            DB::commit();

            $this->closeModal();
            $this->reset(['team1', 'team2', 'map1', 'map2', 'totalEvents']);

            $this->dispatch('match-started', $match->id);

            session()->flash('message', "Матч #{$match->id} создан и запущен!");

            // Обновляем список матчей
            $this->render();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('game')->error('Ошибка создания матча', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->addError('general', 'Ошибка при создании матча: ' . $e->getMessage());
        }
    }

    public function cancelMatch(MatchModel $match)
    {
        $match->update(['status' => 'canceled']);
        $this->dispatch('match-canceled', $match->id);
    }
}
