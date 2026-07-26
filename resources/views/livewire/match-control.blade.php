<div>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-4">⚔️ Управление матчами</h1>

        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <h2 class="text-lg font-semibold mb-2">Создать матч</h2>
            <form wire:submit="startMatch">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Команда 1</label>
                        <select wire:model="team1" class="w-full px-3 py-2 border rounded">
                            <option value="">Выберите команду</option>
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}">{{ $team->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Команда 2</label>
                        <select wire:model="team2" class="w-full px-3 py-2 border rounded">
                            <option value="">Выберите команду</option>
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}">{{ $team->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" class="mt-4 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Создать матч
                </button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <h2 class="text-lg font-semibold p-4 border-b">Список матчей</h2>
            @if($matches->isEmpty())
                <p class="p-4 text-gray-500">Нет матчей</p>
            @else
                <table class="w-full">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left">ID</th>
                        <th class="px-6 py-3 text-left">Команды</th>
                        <th class="px-6 py-3 text-left">Статус</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($matches as $match)
                        <tr class="border-t">
                            <td class="px-6 py-4">#{{ $match->id }}</td>
                            <td class="px-6 py-4">
                                {{ $match->team1->name }} vs {{ $match->team2->name }}
                            </td>
                            <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded text-xs
                                        @if($match->status->value === 'finished') bg-green-100 text-green-800
                                        @elseif($match->status->value === 'in_progress') bg-yellow-100 text-yellow-800
                                        @else bg-blue-100 text-blue-800 @endif">
                                        {{ $match->status->label() }}
                                    </span>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {{ $matches->links() }}
            @endif
        </div>
    </div>
</div>
