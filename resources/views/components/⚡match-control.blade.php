<div class="match-control">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Матчи</h2>
        <button wire:click="openModal" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
            Новый матч
        </button>
    </div>

    <!-- Список матчей -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($matches as $match)
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex justify-between items-center mb-2">
                    <div class="font-bold">
                        Матч #{{ $match->id }}
                    </div>
                    @if ($match->isPending())
                        <div class="text-yellow-500">Ожидает</div>
                    @elseif ($match->isInProgress())
                        <div class="text-blue-500">В процессе</div>
                    @elseif ($match->isFinished())
                        <div class="text-green-500">Завершен</div>
                    @else
                        <div class="text-gray-500">{{ $match->status->label() }}</div>
                    @endif
                </div>

                <div class="mb-3">
                    <div class="flex justify-between">
                        <div class="font-medium">
                            {{ $match->team1->name }}
                        </div>
                        <div class="font-medium">
                            {{ $match->team2->name }}
                        </div>
                    </div>

                    <div class="flex justify-between mt-1">
                        <div class="text-sm text-gray-600">
                            {{ $match->score_team1 }}
                        </div>
                        <div class="text-sm text-gray-600">
                            {{ $match->score_team2 }}
                        </div>
                    </div>
                </div>

                <div class="text-sm">
                    Карты:
                    <div class="flex justify-between mt-1">
                        <div>{{ $match->map1->display_name }}</div>
                        <div>{{ $match->map2->display_name }}</div>
                    </div>
                </div>

                <div class="mt-4 flex justify-between">
                    <div class="text-sm text-gray-500">
                        {{ $match->created_at->format('d.m.Y H:i') }}
                    </div>
                    @if ($match->isPending() && $match->started_at)
                        <div class="text-sm text-gray-500">
                            Запланирован: {{ $match->started_at->format('d.m.Y H:i') }}
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-8">
                <p class="text-gray-500">Нет активных матчей</p>
            </div>
        @endforelse
    </div>

    <!-- Пагинация -->
    <div class="mt-6">
        {{ $matches->links() }}
    </div>

    <!-- Модальное окно создания матча -->
    @if ($isModalOpen)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Создание матча</h3>

                    <div class="mt-2 px-7 py-3">
                        <div class="mb-3">
                            <select wire:model="team1" class="w-full px-3 py-2 mb-3 text-sm border rounded">
                                <option value="">Выберите первую команду</option>
                                @foreach ($teams as $team)
                                    <option value="{{ $team->id }}">
                                        {{ $team->name }}
                                        @if ($team->tag)
                                            ({{ $team->tag }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <select wire:model="team2" class="w-full px-3 py-2 mb-3 text-sm border rounded">
                                <option value="">Выберите вторую команду</option>
                                @foreach ($teams as $team)
                                    <option value="{{ $team->id }}">
                                        {{ $team->name }}
                                        @if ($team->tag)
                                            ({{ $team->tag }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <select wire:model="map1" class="w-full px-3 py-2 mb-3 text-sm border rounded">
                                <option value="">Выберите первую карту</option>
                                @foreach ($maps as $map)
                                    <option value="{{ $map->id }}">
                                        {{ $map->display_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <select wire:model="map2" class="w-full px-3 py-2 mb-3 text-sm border rounded">
                                <option value="">Выберите вторую карту</option>
                                @foreach ($maps as $map)
                                    <option value="{{ $map->id }}">
                                        {{ $map->display_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <select wire:model="totalEvents" class="w-full px-3 py-2 mb-3 text-sm border rounded">
                                @for ($i = 4; $i <= 100; $i += 4)
                                    <option value="{{ $i }}">{{ $i }} событий</option>
                                @endfor
                            </select>
                        </div>

                        @error('team1')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror

                        @error('team2')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="items-center px-4 py-3">
                        <button @click="$wire.closeModal()" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded mr-2">
                            Отмена
                        </button>
                        <button wire:click="startMatch" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                            Запустить
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>