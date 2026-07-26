<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">️ Создание турнира</h1>
        <button wire:click="openModal" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition">
            + Новый турнир
        </button>
    </div>

    <!-- Фильтры -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Поиск турниров..."
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>
            <div class="w-48">
                <select
                    wire:model.live="statusFilter"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">Все статусы</option>
                    <option value="active">Активные</option>
                    <option value="finished">Завершенные</option>
                    <option value="upcoming">Предстоящие</option>
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

    <!-- Список турниров -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($tournaments->isEmpty())
            <div class="p-8 text-center text-gray-500">
                <p class="text-lg">Нет турниров</p>
                <p class="text-sm">Создайте новый турнир</p>
            </div>
        @else
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Название</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Тип</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Команды</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Матчи</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Статус</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($tournaments as $tournament)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    @if($tournament->logo)
                                        <img src="{{ $tournament->logo }}" class="w-8 h-8 mr-2 rounded">
                                    @endif
                                    <span class="font-medium">{{ $tournament->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 bg-gray-100 rounded text-xs">{{ $tournament->type->label() }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($tournament->teams->take(5) as $team)
                                        <span class="px-2 py-1 bg-gray-100 rounded text-xs">
                                            {{ $team->name }}
                                        </span>
                                    @endforeach
                                    @if($tournament->teams->count() > 5)
                                        <span class="px-2 py-1 bg-gray-100 rounded text-xs">
                                            +{{ $tournament->teams->count() - 5 }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-between">
                                    <div>
                                        <div class="text-sm">
                                            {{ $tournament->matches->where('status', '!=', 'finished')->count() }}
                                            <span class="text-gray-500">активных</span>
                                        </div>
                                        <div class="text-sm">
                                            {{ $tournament->matches->where('status', 'finished')->count() }}
                                            <span class="text-gray-500">завершенных</span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-bold">{{ $tournament->totalMatches }}</span>
                                        <span class="text-gray-500">всего</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded text-xs
                                    @if($tournament->isUpcoming()) bg-blue-100 text-blue-800
                                    @elseif($tournament->isActive()) bg-yellow-100 text-yellow-800
                                    @elseif($tournament->isFinished()) bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $tournament->status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <a href="/tournaments/{{ $tournament->id }}/edit" class="text-blue-600 hover:underline">
                                    Редактировать
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-4">
                {{ $tournaments->links() }}
            </div>
        @endif
    </div>

    <!-- Модальное окно создания турнира -->
    @if($isModalOpen)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Создание турнира</h3>
                    <div class="mt-2 px-7 py-3">
                        <form wire:submit="createTournament" class="space-y-4">
                            <div class="mb-4">
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Название</label>
                                <input
                                    type="text"
                                    wire:model="name"
                                    id="name"
                                    class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                @error('name')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Тип</label>
                                <select
                                    wire:model="type"
                                    id="type"
                                    class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    @foreach($types as $type)
                                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="startDate" class="block text-sm font-medium text-gray-700 mb-1">Дата начала</label>
                                <input
                                    type="date"
                                    wire:model="startDate"
                                    id="startDate"
                                    class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                @error('startDate')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="endDate" class="block text-sm font-medium text-gray-700 mb-1">Дата окончания</label>
                                <input
                                    type="date"
                                    wire:model="endDate"
                                    id="endDate"
                                    class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                @error('endDate')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Команды</label>
                                <select
                                    wire:model="selectedTeamIds"
                                    multiple
                                    class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    @foreach($availableTeams as $team)
                                        <option value="{{ $team->id }}">
                                            {{ $team->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('selectedTeamIds')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mt-5">
                                <button
                                    type="submit"
                                    class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition">
                                    Создать
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="items-center px-4 py-3">
                    <button @click="$wire.closeModal()" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded mr-2">
                        Отмена
                    </button>
                    <button wire:click="createTournament" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                        Создать
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>