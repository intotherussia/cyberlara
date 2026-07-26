<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">️ Статистика игрока</h1>
        <div>
            <span class="px-3 py-1 rounded text-sm
                @if($player->isTopKiller()) bg-green-100 text-green-800
                @elseif($player->isTopTeamKiller()) bg-blue-100 text-blue-800
                @else bg-gray-100 text-gray-800 @endif">
                {{ $player->rankLabel() }}
            </span>
        </div>
    </div>

    <!-- Общая статистика -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Общая статистика</h2>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-500">Общее убийств</span>
                    <span class="font-bold">{{ $player->total_kills }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Убийств в матче</span>
                    <span class="font-bold">{{ $player->avg_kills }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Среднее убийств</span>
                    <span class="font-bold">{{ $player->avg_kills }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Смертей</span>
                    <span class="font-bold">{{ $player->deaths }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">K/D</span>
                    <span class="font-bold">{{ $player->kd }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Рейтинг</span>
                    <span class="font-bold">{{ $player->rating }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Карта статистика</h2>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-500">Любимая карта</p>
                    <p class="font-bold">{{ $player->favoriteMap?->display_name ?? 'Нет данных' }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-500">Любимое оружие</p>
                    <p class="font-bold">{{ $player->favoriteWeapon ?? 'Нет данных' }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-500">Всего матчей</p>
                    <p class="font-bold">{{ $player->match_count }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-500">Победы</p>
                    <p class="font-bold">{{ $player->wins }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-500">Процент побед</p>
                    <p class="font-bold">{{ $player->win_rate }}%</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-500">Процент смертей</p>
                    <p class="font-bold">{{ $player->death_rate }}%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Матчи -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Последние матчи</h2>
        @if($matches->isEmpty())
            <p class="text-gray-500 py-4">Нет матчей</p>
        @else
            <div class="space-y-4">
                @foreach($matches as $match)
                    <div class="border-b pb-4">
                        <div class="flex justify-between">
                            <div>
                                <p class="font-medium">
                                    {{ $match->team1->name }}
                                    <span class="text-gray-400 mx-2">vs</span>
                                    {{ $match->team2->name }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    {{ $match->created_at->format('d.m.Y H:i') }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold">{{ $match->kills }}</p>
                                <p class="text-sm text-gray-500">убийств</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- График -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Статистика за месяц</h2>
        <div class="h-40">
            <canvas wire:ignore x-data="" x-init="() => {
                const ctx = document.getElementById('statsChart');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: {{ $monthlyStats['labels'] }},
                        datasets: [{
                            label: 'Убийства',
                            data: {{ $monthlyStats['kills'] }},
                            borderColor: 'rgb(59, 130, 189)',
                            tension: 0.1
                        }, {
                            label: 'Смерти',
                            data: {{ $monthlyStats['deaths'] }},
                            borderColor: 'rgb(200, 30, 89)',
                            tension: 0.1
                        }]
                    });
                }">
                <canvas id="statsChart" width="100%" height="40"></canvas>
            </div>
        </div>
    </div>

    <!-- Лента событий -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Лента событий</h2>
        <div class="max-h-[400px] overflow-y-auto space-y-2">
            @forelse($events as $event)
                <div class="border-b border-gray-100 pb-2 last:border-0">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">{{ $event->created_at->format('H:i:s') }}</span>
                        <span class="font-medium">
                            {{ $event->location }}
                        </span>
                    </div>
                    <div class="text-sm">
                        @if($event->kills->isNotEmpty())
                            @foreach($event->kills as $kill)
                                <span class="text-green-600 font-medium">{{ $kill->killer->name }}</span>
                                <span class="text-gray-400 mx-2">—</span>
                                <span class="text-red-600 font-medium">{{ $kill->victim->name }}</span>
                                <span class="text-xs text-gray-400 ml-2">({{ $kill->weapon }})</span>
                            @endforeach
                        @else
                            <span class="text-gray-500">Никто не убит</span>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-gray-500 py-4">Нет событий</p>
            @endforelse
        </div>
    </div>
</div>