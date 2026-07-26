<div class="container mx-auto px-4 py-8">
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
        <div class="flex items-center gap-3">
            <!-- Статус подключения WebSocket -->
            <div class="flex items-center gap-2" id="ws-indicator">
                <div class="w-3 h-3 rounded-full bg-green-500" id="ws-dot"></div>
                <span class="text-xs text-gray-500" id="ws-status">connected</span>
            </div>
            <!-- Статус матча -->
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
        <div class="bg-gray-50 rounded-lg p-4 text-center {{ $match->current_map_index == 0 ? 'border-2 border-blue-500 shadow-md' : '' }}">
            <p class="text-sm text-gray-500">Карта 1</p>
            <p class="font-bold {{ $match->current_map_index == 0 ? 'text-blue-600' : '' }}">
                {{ $match->map1->display_name }}
            </p>
            @if($match->current_map_index == 0 && !$isFinished)
                <span class="text-xs text-blue-600 font-medium">🟢 Текущая</span>
            @elseif($match->current_map_index == 1 && $match->current_event > 0)
                <span class="text-xs text-gray-400">✅ Сыграна</span>
            @elseif($isFinished)
                <span class="text-xs text-gray-400">✅ Сыграна</span>
            @else
                <span class="text-xs text-gray-400">⏳ Ожидает</span>
            @endif
            <div class="text-xs text-gray-400 mt-1">
                Событий: {{ $match->eventsPerMap() }}
            </div>
        </div>
        <div class="bg-gray-50 rounded-lg p-4 text-center {{ $match->current_map_index == 1 ? 'border-2 border-blue-500 shadow-md' : '' }}">
            <p class="text-sm text-gray-500">Карта 2</p>
            <p class="font-bold {{ $match->current_map_index == 1 ? 'text-blue-600' : '' }}">
                {{ $match->map2->display_name }}
            </p>
            @if($match->current_map_index == 1 && !$isFinished)
                <span class="text-xs text-blue-600 font-medium">🟢 Текущая</span>
            @elseif($match->current_map_index == 0 && $match->current_event > 0)
                <span class="text-xs text-gray-400">⏳ Ожидает</span>
            @elseif($isFinished)
                <span class="text-xs text-gray-400">✅ Сыграна</span>
            @else
                <span class="text-xs text-gray-400">⏳ Ожидает</span>
            @endif
            <div class="text-xs text-gray-400 mt-1">
                Событий: {{ $match->eventsPerMap() }}
            </div>
        </div>
    </div>

    <!-- Прогресс и счет -->
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
    </div>

    <!-- Лента событий -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">📋 Лента событий</h3>
            <span class="text-sm text-gray-500">Всего: {{ count($events) }}</span>
        </div>
        <div class="space-y-2 max-h-96 overflow-y-auto" id="events-feed">
            @forelse(array_reverse($events) as $event)
                <div class="border-b border-gray-100 pb-2 last:border-0 hover:bg-gray-50 p-2 rounded transition-colors
                            {{ $loop->last && !$isFinished ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}">
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

                                <span class="font-medium {{ $isKillerTeam1 ? 'text-blue-600' : 'text-red-600' }}">
                                    [{{ isset($kill['killer_team_name']) && is_string($kill['killer_team_name']) ? $kill['killer_team_name'] : '???' }}]
                                    {{ isset($kill['killer']) && is_string($kill['killer']) ? $kill['killer'] : '???' }}
                                </span>

                                <span class="text-gray-400">🔫</span>

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
                    <div class="text-xs text-gray-400 mt-1">
                        {{ isset($event['created_at']) ? $event['created_at'] : '' }}
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center py-8">⏳ Событий пока нет</p>
            @endforelse
        </div>
    </div>

    <!-- Команды -->
    <div class="grid grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="font-semibold mb-3 text-blue-600">🏆 {{ $match->team1->name }}</h4>
            <ul class="space-y-1 text-sm">
                @foreach($match->team1->players as $player)
                    <li class="flex justify-between items-center">
                        <span>{{ $player->name }}</span>
                        <span class="text-gray-500 text-xs">
                            K: <span class="font-medium text-green-600">{{ $match->kills()->where('killer_player_id', $player->id)->count() }}</span>
                            /
                            D: <span class="font-medium text-red-600">{{ $match->kills()->where('victim_player_id', $player->id)->count() }}</span>
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="font-semibold mb-3 text-red-600">🏆 {{ $match->team2->name }}</h4>
            <ul class="space-y-1 text-sm">
                @foreach($match->team2->players as $player)
                    <li class="flex justify-between items-center">
                        <span>{{ $player->name }}</span>
                        <span class="text-gray-500 text-xs">
                            K: <span class="font-medium text-green-600">{{ $match->kills()->where('killer_player_id', $player->id)->count() }}</span>
                            /
                            D: <span class="font-medium text-red-600">{{ $match->kills()->where('victim_player_id', $player->id)->count() }}</span>
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <!-- Победитель -->
    @if($isFinished && $match->winner)
        <div class="mt-6 bg-green-50 border border-green-200 rounded-lg p-4 text-center animate-pulse">
            <p class="text-lg font-bold text-green-700">
                🏆 Победитель: {{ $match->winner->name }}
            </p>
            <p class="text-green-600">Финальный счет: {{ $score }}</p>
        </div>
    @endif

    <!-- Кнопка обновления (только для отладки) -->
    <div class="mt-6 text-center">
        <button wire:click="refreshMatch" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded transition text-sm">
            🔄 Обновить вручную
        </button>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:init', function () {
            console.log('🎯 Livewire initialized for match {{ $match->id }}');

            // Автоскролл к новым событиям
            const eventsFeed = document.getElementById('events-feed');

            if (window.Echo) {
                const channel = window.Echo.channel('match.{{ $match->id }}');

                channel.listen('match.event', function (data) {
                    console.log('📡 Event received via WebSocket:', data);
                    Livewire.dispatch('match.event', data);

                    // Скролл к последнему событию
                    setTimeout(() => {
                        if (eventsFeed) {
                            eventsFeed.scrollTop = eventsFeed.scrollHeight;
                        }
                    }, 100);
                });

                channel.listen('match.finished', function (data) {
                    console.log('🏁 Match finished:', data);
                    Livewire.dispatch('match.finished', data);
                });

                console.log('✅ Subscribed to match.{{ $match->id }}');
            } else {
                console.warn('⚠️ Echo not available');
            }
        });

        function updateWebSocketIndicator() {
            const dot = document.getElementById('ws-dot');
            const statusEl = document.getElementById('ws-status');

            if (window.Echo && window.Echo.connector) {
                const state = window.Echo.connector.pusher.connection.state || 'disconnected';
                if (statusEl) statusEl.textContent = state;

                if (dot) {
                    switch (state) {
                        case 'connected':
                            dot.className = 'w-3 h-3 rounded-full bg-green-500';
                            break;
                        case 'connecting':
                            dot.className = 'w-3 h-3 rounded-full bg-yellow-500';
                            break;
                        default:
                            dot.className = 'w-3 h-3 rounded-full bg-red-500';
                    }
                }
            }
        }

        // Обновляем индикатор каждые 3 секунды
        setInterval(updateWebSocketIndicator, 3000);
        updateWebSocketIndicator();
    </script>
@endpush
