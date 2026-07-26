<div class="tournament-list">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Турниры</h2>
        <button wire:click="openModal" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
            Новый турнир
        </button>
    </div>

    <!-- Список турниров -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($tournaments as $tournament)
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex justify-between items-center mb-2">
                    <div class="font-bold text-lg">
                        {{ $tournament->name }}
                    </div>
                    @if ($tournament->is_active)
                        <div class="text-green-500">Активен</div>
                    @else
                        <div class="text-gray-500">Не активен</div>
                    @endif
                </div>

                <div class="mb-3">
                    <div class="text-sm text-gray-600">
                        Тип: {{ $tournament->type_label }}
                    </div>
                </div>

                <div class="text-sm">
                    Матчи: {{ $tournament->matches_count }}
                </div>

                <div class="mt-4 flex justify-between">
                    <div class="text-sm text-gray-500">
                        {{ $tournament->created_at->format('d.m.Y H:i') }}
                    </div>
                    @if ($tournament->started_at)
                        <div class="text-sm text-gray-500">
                            Начало: {{ $tournament->started_at->format('d.m.Y H:i') }}
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-8">
                <p class="text-gray-500">Нет созданных турниров</p>
            </div>
        @endforelse
    </div>

    <!-- Пагинация -->
    <div class="mt-6">
        {{ $tournaments->links() }}
    </div>

    <!-- Модальное окно создания турнира -->
    @if ($isModalOpen)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Создание турнира</h3>

                    <div class="mt-2 px-7 py-3">
                        <div class="mb-3">
                            <input wire:model="name" type="text" placeholder="Название турнира" class="w-full px-3 py-2 mb-3 text-sm border rounded">

                            @error('name')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <select wire:model="type" class="w-full px-3 py-2 mb-3 text-sm border rounded">
                                <option value="custom">Пользовательский</option>
                                <option value="global">Глобальный</option>
                            </select>

                            @error('type')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <input wire:model="days" type="number" placeholder="Дней на расписание" class="w-full px-3 py-2 mb-3 text-sm border rounded">

                            @error('days')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
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
        </div>
    @endif
</div>