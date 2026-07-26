<div wire:init="loadProgress" wire:poll.5s="refreshMatch" class="container mx-auto px-4 py-8">
    <!-- Заголовок -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">⚔️ Матч #{{ $match->id }}</h1>
            <p class="text-gray-600">
                {{ $match->team1->name }}
                <span class="text-gray-400 mx-2">vs</span>
                {{ $match->team2->name }}
            </p>
        </div>
        <div>
            <span class="px-3 py-1 rounded text-sm
                @if($isFinished) bg-green-100 text-green-800
                @elseif($isInProgress) bg-yellow-100 text-yellow-800
                @else bg-blue-100 text-blue-800 @endif">
                {{ $match->status->label() }}
            </span>
        </div>
    </div>

    <!-- Карты -->
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-gray-50 rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500">Карта 1</p>
            <p class="font-bold">{{ $match->map1->display_name }}</p>
        </div>
        <div class="bg-gray-50 rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500">Карта 2</p>
            <p class="font-bold">{{ $match->map2->display_name }}</p>
        </div>
    </div>

    <!-- Прогресс -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex justify-between items-center mb-2">
            <span class="text-sm text-gray-600">Прогресс матча</span>
            <span class="text-sm font-medium">{{ $progress }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2.5">
            <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-500"
                 style="width: {{ $progress }}%"></div>
        </div>
        <div class="flex justify-between items-center mt-2 text-sm text-gray-500">
            <span>Событие {{ $currentEvent }} из {{ $totalEvents }}</span>
            <span class="font-bold text-lg">{{ $score }}</span>
        </div>
        <div class="text-center text-xs text-gray-400 mt-2" x-data="{ countdown: 5 }" x-init="setInterval(() => { countdown = countdown > 0 ? countdown - 1 : 5; }, 1000)">
            ⏳ Обновление через <span x-text="countdown" class="font-bold text-blue-600"></span> сек...
        </div>
    </div>

    <!-- Лента событий -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold mb-4">📋 Лента событий</h3>
        <div class="space-y-2" id="events-feed">
            @forelse(array_reverse($events) as $event)
                <div class="border-b border-gray-100 pb-2 last:border-0">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Событие #{{ $event['event_number'] ?? 0 }}</span>
                        <span class="text-gray-400">
                        {{ isset($event['location']) && is_string($event['location']) ? $event['location'] : 'Неизвестно' }}
                    </span>
                    </div>
                    <div class="text-sm">
                        @if(!empty($event['kills']) && is_array($event['kills']) && count($event['kills']) > 0)
                            @foreach($event['kills'] as $kill)
                                @php
                                    $team1Id = $match->team1->id ?? null;
                                    $isKillerTeam1 = isset($kill['killer_team_id']) && $kill['killer_team_id'] == $team1Id;
                                    $isVictimTeam1 = isset($kill['victim_team_id']) && $kill['victim_team_id'] == $team1Id;
                                @endphp

                                {{-- Убийца --}}
                                <span class="font-medium {{ $isKillerTeam1 ? 'text-blue-600' : 'text-red-600' }}">
                                [{{ isset($kill['killer_team_name']) && is_string($kill['killer_team_name']) ? $kill['killer_team_name'] : '???' }}]
                                {{ isset($kill['killer']) && is_string($kill['killer']) ? $kill['killer'] : '???' }}
                            </span>

                                <span class="text-gray-400">🔫</span>

                                {{-- Жертва --}}
                                <span class="font-medium {{ $isVictimTeam1 ? 'text-blue-600' : 'text-red-600' }}">
                                [{{ isset($kill['victim_team_name']) && is_string($kill['victim_team_name']) ? $kill['victim_team_name'] : '???' }}]
                                {{ isset($kill['victim']) && is_string($kill['victim']) ? $kill['victim'] : '???' }}
                            </span>

                                <span class="text-xs text-gray-400 ml-2">
                                ({{ isset($kill['weapon']) && is_string($kill['weapon']) ? $kill['weapon'] : '?' }})
                            </span>
                                <br>
                            @endforeach
                        @else
                            <span class="text-gray-500">Никто не убит</span>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center py-4">⏳ Событий пока нет</p>
            @endforelse
        </div>
    </div>

    <!-- Команды -->
    <div class="grid grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="font-semibold mb-3">🏆 {{ $match->team1->name }}</h4>
            <ul class="space-y-1 text-sm">
                @foreach($match->team1->players as $player)
                    <li class="flex justify-between">
                        <span>{{ $player->name }}</span>
                        <span class="text-gray-500">
                            K: {{ $match->kills()->where('killer_player_id', $player->id)->count() }} /
                            D: {{ $match->kills()->where('victim_player_id', $player->id)->count() }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="font-semibold mb-3">🏆 {{ $match->team2->name }}</h4>
            <ul class="space-y-1 text-sm">
                @foreach($match->team2->players as $player)
                    <li class="flex justify-between">
                        <span>{{ $player->name }}</span>
                        <span class="text-gray-500">
                            K: {{ $match->kills()->where('killer_player_id', $player->id)->count() }} /
                            D: {{ $match->kills()->where('victim_player_id', $player->id)->count() }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <!-- Победитель -->
    @if($isFinished && $match->winner)
        <div class="mt-6 bg-green-50 border border-green-200 rounded-lg p-4 text-center">
            <p class="text-lg font-bold text-green-700">
                🏆 Победитель: {{ $match->winner->name }}
            </p>
            <p class="text-green-600">Финальный счет: {{ $score }}</p>
        </div>
    @endif

    <!-- Кнопка обновления -->
    <div class="mt-6 text-center">
        <button wire:click="refreshMatch" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded transition">
            🔄 Обновить
        </button>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:init', function () {
            if (window.Echo) {
                const channel = window.Echo.channel('match.{{ $match->id }}');

                channel.listen('match.event', function (data) {
                    Livewire.dispatch('match.event', data);
                });

                channel.listen('match.finished', function (data) {
                    Livewire.dispatch('match.finished', data);
                });
            }
        });
    </script>
@endpush
