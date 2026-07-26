<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Quake 3 Manager</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body>
<nav class="bg-gray-800 text-white p-4">
    <div class="container mx-auto flex justify-between items-center">
        <a href="/" class="text-xl font-bold">🎮 Q3 Manager</a>
        <ul class="flex space-x-4">
            <li><a href="/" class="hover:text-gray-300">Главная</a></li>
            <li><a href="/matches" class="hover:text-gray-300">Матчи</a></li>
            <li><a href="/teams" class="hover:text-gray-300">Команды</a></li>
            <li><a href="/tournaments" class="hover:text-gray-300">Турниры</a></li>
        </ul>
    </div>
</nav>

<main>
    {{ $slot }}
</main>

@livewireScripts
@stack('scripts')
</body>
</html>
