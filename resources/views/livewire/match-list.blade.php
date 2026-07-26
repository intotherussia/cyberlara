<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">️ Список матчей</h1>
        <a href="/matches/create" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition">
            + Создать матч
        </a>
    </div>

    <!-- Фильтры -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Поиск по командам..."
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>
            <div class="w-48">
                <select
                    wire:model.live="statusFilter"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">Все статусы</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <button
                wire:click="resetFilters"
                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded transition"
            >
                Сбросить
            </button>
        </div>
    </div>

    <!-- Список матчей -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($matches->isEmpty())
            <div class="p-8 text-center text-gray-500">
                <p class="text-lg">Нет матчей</p>
                <p class="text-sm">Создайте новый матч или дождитесь генерации расписания</p>
            </div>
        @else
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Команды</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Счет</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Карты</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Статус</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($matches as $match)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm">#{{ $match->id }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="font-medium">{{ $match->team1->name }}</span>
                                <span class="text-gray-400 mx-2">vs</span>
                                <span class="font-medium">{{ $match->team2->name }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm font-bold">
                                {{ $match->score_team1 }} : {{ $match->score_team2 }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                {{ $match->map1->display_name }}<br>
                                <span class="text-gray-500">{{ $match->map2->display_name }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-2 py-1 rounded text-xs
                                    @if($match->status->value === 'finished') bg-green-100 text-green-800
                                    @elseif($match->status->value === 'in_progress') bg-yellow-100 text-yellow-800
                                    @elseif($match->status->value === 'pending') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $match->status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <a href="/matches/{{ $match->id }}" class="text-blue-600 hover:underline">
                                    Просмотр
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-4">
                {{ $matches->links() }}
            </div>
        @endif
    </div>
</div>