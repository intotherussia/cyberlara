<?php

use App\Livewire\Dashboard;
use App\Livewire\MatchList;
use App\Livewire\MatchView;
use App\Livewire\Components\MatchControl;
use App\Livewire\Components\TeamManagement;
use App\Livewire\Components\TournamentList;
use App\Livewire\PlayerStats;
use Illuminate\Support\Facades\Route;

// Главная страница
Route::get('/', Dashboard::class)->name('dashboard');

// Матчи
Route::get('/matches', MatchList::class)->name('matches.index');
Route::get('/matches/create', MatchControl::class)->name('matches.create');
Route::get('/matches/{id}', MatchView::class)->name('matches.show');

// Команды
Route::get('/teams', TeamManagement::class)->name('teams.index');
Route::get('/teams/create', TeamManagement::class)->name('teams.create');

// Турниры
Route::get('/tournaments', TournamentList::class)->name('tournaments.index');

// Игроки
Route::get('/players/{id}', PlayerStats::class)->name('players.show');
