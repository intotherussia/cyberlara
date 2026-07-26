<?php

namespace App\Livewire\Components;

use App\Domains\Game\Models\Tournament;
use App\Domains\Game\Services\TournamentService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class TournamentList extends Component
{
    use WithPagination;

    public $name;
    public $type = 'custom';
    public $days = 7;
    public $isModalOpen = false;
    public $search = '';

    protected $rules = [
        'name' => 'required|string|max:255|unique:tournaments,name',
        'type' => 'required|in:custom,global',
        'days' => 'integer|min:1|max:30',
    ];

    public function render()
    {
        $tournaments = Tournament::with(['matches', 'teams'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.tournament-list', [
            'tournaments' => $tournaments
        ]);
    }

    public function openModal()
    {
        $this->reset(['name', 'type', 'days', 'isModalOpen']);
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
    }

    public function createTournament()
    {
        $this->validate();

        Tournament::create([
            'name' => $this->name,
            'type' => $this->type,
            'rules' => ['format' => 'tdm', 'maps' => 2],
            'is_active' => true,
            'started_at' => now(),
            'created_by' => Auth::id(),
        ]);

        $this->closeModal();
        $this->reset(['name', 'type', 'days']);

        session()->flash('message', "Турнир '{$this->name}' успешно создан!");
    }

    public function generateSchedule(Tournament $tournament, TournamentService $service)
    {
        $this->validateOnly('days');

        try {
            $service->generateSchedule($tournament, $this->days);

            session()->flash('message', "Расписание для турнира '{$tournament->name}' сгенерировано на {$this->days} дней!");
            $this->reset('days');
        } catch (\Exception $e) {
            $this->addError('general', 'Ошибка при генерации расписания: ' . $e->getMessage());
        }
    }

    public function deleteTournament(Tournament $tournament)
    {
        if ($tournament->created_by !== Auth::id()) {
            $this->addError('general', 'Вы не можете удалить этот турнир');
            return;
        }

        $tournament->delete();
        session()->flash('message', "Турнир удален");
    }

    public function resetFilters()
    {
        $this->reset(['search']);
        $this->resetPage();
    }
}
