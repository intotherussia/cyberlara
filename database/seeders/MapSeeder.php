<?php

namespace Database\Seeders;

use App\Domains\Game\Models\Map;
use App\Domains\Game\Models\Location;
use Illuminate\Database\Seeder;

class MapSeeder extends Seeder
{
    public function run(): void
    {
        $maps = [
            [
                'name' => 'dm7',
                'display_name' => 'DM7 - The Edge',
                'description' => 'Классическая карта с множеством уровней',
                'locations' => [
                    ['name' => 'Rail Platform', 'priority_weapon' => 'rail'],
                    ['name' => 'Rocket Area', 'priority_weapon' => 'rocket'],
                    ['name' => 'Plasma Gun', 'priority_weapon' => 'mg_plasma'],
                    ['name' => 'Red Armor', 'priority_weapon' => 'rocket'],
                    ['name' => 'Yellow Armor', 'priority_weapon' => 'shaft'],
                ]
            ],
            [
                'name' => 'campgrounds',
                'display_name' => 'Campgrounds',
                'description' => 'Открытая карта с видом на горы',
                'locations' => [
                    ['name' => 'Rail Bridge', 'priority_weapon' => 'rail'],
                    ['name' => 'Rocket Launcher', 'priority_weapon' => 'rocket'],
                    ['name' => 'Lightning Gun', 'priority_weapon' => 'shaft'],
                    ['name' => 'Mega Health', 'priority_weapon' => 'mg_plasma'],
                    ['name' => 'Red Armor', 'priority_weapon' => 'rocket'],
                ]
            ],
            [
                'name' => 'q3dm6',
                'display_name' => 'Q3DM6',
                'description' => 'Карта с классической планировкой',
                'locations' => [
                    ['name' => 'Rail Gun', 'priority_weapon' => 'rail'],
                    ['name' => 'Rocket Jump', 'priority_weapon' => 'rocket'],
                    ['name' => 'Shaft Room', 'priority_weapon' => 'shaft'],
                    ['name' => 'Plasma Hall', 'priority_weapon' => 'mg_plasma'],
                    ['name' => 'Armor', 'priority_weapon' => 'rocket'],
                ]
            ],
            [
                'name' => 'ztn3tourney1',
                'display_name' => 'ZTN3 Tourney 1',
                'description' => 'Карта для турнирных матчей',
                'locations' => [
                    ['name' => 'Rail Corner', 'priority_weapon' => 'rail'],
                    ['name' => 'Rocket Center', 'priority_weapon' => 'rocket'],
                    ['name' => 'LG Platform', 'priority_weapon' => 'shaft'],
                    ['name' => 'MG Area', 'priority_weapon' => 'mg_plasma'],
                ]
            ],
            [
                'name' => 'pro-q3dm13',
                'display_name' => 'Pro Q3DM13',
                'description' => 'Профессиональная карта для соревнований',
                'locations' => [
                    ['name' => 'Rail Alley', 'priority_weapon' => 'rail'],
                    ['name' => 'Rocket Room', 'priority_weapon' => 'rocket'],
                    ['name' => 'Shaft Tunnel', 'priority_weapon' => 'shaft'],
                    ['name' => 'Plasma Bridge', 'priority_weapon' => 'mg_plasma'],
                    ['name' => 'Red Armor', 'priority_weapon' => 'rocket'],
                    ['name' => 'Yellow Armor', 'priority_weapon' => 'shaft'],
                ]
            ],
        ];

        foreach ($maps as $mapData) {
            $map = Map::create([
                'name' => $mapData['name'],
                'display_name' => $mapData['display_name'],
                'description' => $mapData['description'],
                'is_active' => true,
            ]);

            foreach ($mapData['locations'] as $locationData) {
                Location::create([
                    'map_id' => $map->id,
                    'name' => $locationData['name'],
                    'priority_weapon' => $locationData['priority_weapon'],
                    'bonus' => 1.2,
                ]);
            }
        }
    }
}