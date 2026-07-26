<?php

namespace App\Console\Commands;

use App\Domains\Game\Models\Team;
use App\Domains\Game\Models\User;
use App\Domains\Game\Services\PlayerGeneratorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use function Laravel\Prompts{
    text,
    select,
    confirm,
    note,
    info,
    error,
    table,
    progress
};

class GameCreateTeamCommand extends Command
{
    protected $signature = 'game:create-team';
    protected $description = 'Создание новой команды с игроками';

    public function handle(PlayerGeneratorService $playerGenerator): int
    {
        note("️  СОЗДАНИЕ НОВОЙ КОМАНДЫ\n");
 
         // Ввод данных
         $name = text(
             label: 'Введите название команды',
             required: true,
             validate: function (string $value) {
                 if (Team::where('name', $value)->exists()) {
                     return 'Команда с таким названием уже существует';
                 }
                 return null;
             }
         );
 
         $tag = text(
             label: 'Введите тег команды (3-4 символа)',
             required: false,
             validate: function (string $value) {
                 if (strlen($value) > 0 && (strlen($value) < 2 || strlen($value) > 4)) {
                     return 'Тег должен быть 2-4 символа';
                 }
                 return null;
             }
         );
 
         // Выбор владельца
         $ownerId = select(
             label: 'Выберите владельца команды',
             options: User::pluck('name', 'id')->toArray(),
             required: true
         );
 
         // Количество игроков
         $playerCount = select(
             label: 'Количество игроков для генерации',
             options => [
                 '4' => '4 игрока (минимальный состав)',
                 '6' => '6 игроков (стандартный состав)',
                 '8' => '8 игроков (расширенный состав)',
             ],
             default: '6',
         );
 
         // Подтверждение
         note(sprintf(
             " СОСТАВ:\n\n" .
             "Название: %s\n" .
             "Тег: %s\n" .
             "Владелец: ID %s\n" .
             "Игроков: %s\n",
             $name,
             $tag ?: '(нет)',
             $ownerId,
             $playerCount
         ));
 
         if (!confirm(" Создать команду с этими данными?")) {
             info(" Создание отменено");
             return self::FAILURE;
         }
 
         // Создаем команду
         $team = Team::create([
             'name' => $name,
             'tag' => $tag,
             'owner_id' => $ownerId,
             'wins' => 0,
             'losses' => 0,
             'total_matches' => 0,
         ]);
 
         // Генерируем игроков
         info(" Генерация игроков...");
         $players = $playerGenerator->generateTeamPlayers($name, $team->id, (int) $playerCount);
 
         // Вывод результата
         info(" Команда '{$name}' создана!");
         note(" Сгенерировано игроков: " . count($players));
 
         $tableData = [];
         foreach ($players as $player) {
             $tableData[] = [
                 $player->name,
                 $player->aim,
                 $player->skill,
                 $player->movement,
                 $player->overallRating,
                 $player->primaryStat,
             ];
         }
 
         table(
             ['Игрок', 'Aim', 'Skill', 'Movement', 'Рейтинг', 'Основной навык'],
             $tableData
         );
 
         return self::SUCCESS;
     }
 }
 
 /* Используемая команда:
  * php artisan make:command GameCreateTeamCommand
  * php artisan make:command GameSimulateMatchCommand
  * php artisan make:command GameGenerateScheduleCommand
  * php artisan make:command GameProcessMatchesCommand
  */
 