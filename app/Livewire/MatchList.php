<?php

namespace App\Livewire;

use App\Domains\Game\Models\MatchModel;
use App\Domains\Game\Enums\MatchStatus;
use Livewire\Component;
use Livewire\WithPagination;

class MatchList extends Component
{
    use WithPagination;

    public string $statusFilter = '';
    public string $search = '';

    protected $queryString = [
        'statusFilter' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function render()
    {
        $query = MatchModel::with(['team1', 'team2', 'winner', 'map1', 'map2']);

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('team1', function ($q) {
                    $q->where('name', 'like', "%{$this->search}%");
                })->orWhereHas('team2', function ($q) {
                    $q->where('name', 'like', "%{$this->search}%");
                });
            });
        }

        $matches = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('livewire.match-list', [
            'matches' => $matches,
            'statuses' => MatchStatus::cases(),
        ]);
    }

    public function resetFilters(): void
    {
        $this->reset(['statusFilter', 'search']);
        $this->resetPage();
    }
}
