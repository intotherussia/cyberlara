<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">️ Управление командами</h1>
        <button wire:click="openModal" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition">
            + Создать команду
        </button>
    </div>

    <!-- Фильтры -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Поиск команд..."
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
                    <option value="inactive">Неактивные</option>
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

    <!-- Список команд -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($teams->isEmpty())
            <div class="p-8 text-center text-gray-500">
                <p class="text-lg">Нет команд</p>
                <p class="text-sm">Создайте новую команду</p>
            </div>
        @else
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Название</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Тег</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Победы</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Игроки</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($teams as $team)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium">
                                <div class="flex items-center">
                                    @if($team->logo)
                                        <img src="{{ $team->logo }}" class="w-8 h-8 mr-2 rounded">
                                    @endif
                                    {{ $team->name }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 bg-gray-100 rounded text-xs">{{ $team->tag }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <span class="font-bold">{{ $team->wins }}</span>
                                    <span class="text-gray-400 mx-2">побед</span>
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $team->winRate }}% побед
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($team->players as $player)
                                        <span class="px-2 py-1 bg-gray-100 rounded text-xs">
                                            {{ $player->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <a href="/teams/{{ $team->id }}/edit" class="text-blue-600 hover:underline">
                                    Редактировать
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-4">
                {{ $teams->links() }}
            </div>
        @endif
    </div>

    <!-- Модальное окно создания команды -->
    @if($isModalOpen)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Создание команды</h3>
                    <div class="mt-2 px-7 py-3">
                        <form wire:submit="createTeam" class="space-y-4">
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
                                <label for="tag" class="block text-sm font-medium text-gray-700 mb-1">Тег</label>
                                <input
                                    type="text"
                                    wire:model="tag"
                                    id="tag"
                                    class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                @error('tag')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Логотип</label>
                                <div class="mt-1">
                                    <input
                                        type="file"
                                        wire:model="logo"
                                        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    >
                                    @error('logo')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="rank" class="block text-sm font-medium text-gray-700 mb-1">Ранг</label>
                                <input
                                    type="number"
                                    wire:model="rank"
                                    id="rank"
                                    class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                @error('rank')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Игроки</label>
                                <select
                                    wire:model="playerIds"
                                    multiple
                                    class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    @foreach($allPlayers as $player)
                                        <option value="{{ $player->id }}">
                                            {{ $player->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('playerIds')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mt-5">
                                <button
                                    type="submit"
                                    class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition"
                                >
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
                    <button wire:click="createTeam" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                        Создать
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
