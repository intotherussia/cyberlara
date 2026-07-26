<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">Quake 3 Arena Manager</h1>
        <div class="flex space-x-4">
            <a href="/teams/create" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition">
                Создать команду
            </a>
            <a href="/matches/create" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded transition">
                Создать матч
            </a>
        </div>
    </div>

    <!-- Глобальный чемпионат -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg p-6 mb-8">
        <h2 class="text-2xl font-bold">{{ $globalTournament?->name ?? 'Глобальный чемпионат' }}</h2>
        <p class="text-purple-100">Следи за расписанием и результатами</p>
    </div>

    <!-- Топ-5 команд и игроков -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Топ-5 команд</h3>
            <ul class="space-y-2">
                @foreach($topTeams as $team)
                    <li class="flex justify-between items-center">
                        <span class="font-medium">{{ $team->name }}</span>
                        <span class="text-sm text-gray-600">Побед: {{ $team->wins }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Топ-5 игроков</h3>
            <ul class="space-y-2">
                @foreach($topPlayers as $player)
                    <li class="flex justify-between items-center">
                        <span class="font-medium">{{ $player->name }}</span>
                        <span class="text-sm text-gray-600">Убийств: {{ $player->kills }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <!-- Расписание -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h3 class="text-lg font-semibold mb-4">Ближайшие матчи</h3>
        @if($upcomingMatches->isEmpty())
            <p class="text-gray-500">Нет запланированных матчей</p>
        @else
            <ul class="space-y-2">
                @foreach($upcomingMatches as $match)
                    <li class="flex justify-between items-center border-b pb-2">
                        <div>
                            <span class="font-medium">{{ $match->team1->name }}</span>
                            <span class="text-gray-400 mx-2">vs</span>
                            <span class="font-medium">{{ $match->team2->name }}</span>
                        </div>
                        <div class="text-sm text-gray-600">
                            {{ $match->started_at?->format('d.m.Y H:i') ?? 'Дата не указана' }}
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <!-- Результаты -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Результаты последних матчей</h3>
        @if($recentMatches->isEmpty())
            <p class="text-gray-500">Нет завершенных матчей</p>
        @else
            <ul class="space-y-2">
                @foreach($recentMatches as $match)
                    <li class="flex justify-between items-center border-b pb-2">
                        <div>
                            <span class="font-medium">{{ $match->team1->name }}</span>
                            <span class="text-gray-400 mx-2">
                                {{ $match->score_team1 }} : {{ $match->score_team2 }}
                            </span>
                            <span class="font-medium">{{ $match->team2->name }}</span>
                            @if($match->winner)
                                <span class="ml-2 text-sm text-green-600">{{ $match->winner->name }}</span>
                            @endif
                        </div>
                        <div class="text-sm text-gray-600">
                            {{ $match->finished_at?->format('d.m.Y H:i') }}
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>