<?php

namespace App\Livewire\Components;

use App\Domains\Game\Models\Team;
use App\Domains\Game\Models\Player;
use App\Models\User;
use App\Domains\Game\Services\PlayerGeneratorService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class TeamManagement extends Component
{
    use WithPagination;

    public $name;
    public $tag;
    public $ownerId;
    public $playerCount = '6';
    public $isModalOpen = false;
    public $search = '';

    protected $rules = [
        'name' => 'required|string|max:255|unique:teams,name',
        'tag' => 'nullable|string|max:4',
        'ownerId' => 'required|exists:users,id',
        'playerCount' => 'required|in:4,6,8',
    ];

    public function render()
    {
        $teams = Team::with(['owner', 'players'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('tag', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $users = User::all();
        $allPlayers = Player::all();  // ← Добавлено

        return view('livewire.team-management', [
            'teams' => $teams,
            'users' => $users,
            'allPlayers' => $allPlayers,  // ← Добавлено
        ]);
    }

    public function openModal()
    {
        $this->ownerId = Auth::id();
        $this->reset(['name', 'tag', 'playerCount', 'isModalOpen']);
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
    }

    public function createTeam(PlayerGeneratorService $playerGenerator)
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $team = Team::create([
                'name' => $this->name,
                'tag' => $this->tag,
                'owner_id' => $this->ownerId ?? Auth::id(),
                'wins' => 0,
                'losses' => 0,
                'total_matches' => 0,
            ]);

            $playerGenerator->generateTeamPlayers($this->name, $team->id, (int)$this->playerCount);

            DB::commit();

            $this->closeModal();
            $this->reset(['name', 'tag', 'playerCount']);

            $this->dispatch('team-created', $team->id);
            session()->flash('message', "Команда '{$team->name}' успешно создана!");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('general', 'Ошибка при создании команды: ' . $e->getMessage());
        }
    }

    public function deleteTeam(Team $team)
    {
        if ($team->owner_id !== Auth::id()) {
            $this->addError('general', 'Вы не можете удалить эту команду');
            return;
        }

        $team->delete();
        $this->dispatch('team-deleted', $team->id);
        session()->flash('message', "Команда удалена");
    }

    public function resetFilters()
    {
        $this->reset(['search']);
        $this->resetPage();
    }
}
