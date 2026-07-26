<?php

namespace App\Livewire;

use App\Domains\Game\Models\Player;
use Livewire\Component;

class PlayerStats extends Component
{
    public Player $player;

    public function mount(int $id): void
    {
        $this->player = Player::with(['team', 'favoriteMap', 'weaponStats', 'matchStats'])
            ->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.player-stats', [
            'player' => $this->player,
        ]);
    }
}
