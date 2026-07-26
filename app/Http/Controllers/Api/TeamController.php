<?php

namespace App\Http\Controllers\Api;

use App\Domains\Game\Models\Team;
use App\Http\Controllers\Controller;
use App\Http\Resources\TeamResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class TeamController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum', except: ['index', 'show']),
            new Middleware('throttle:60,1'),
        ];
    }

    /**
     * Список команд с рейтингом
     */
    public function index(): JsonResponse
    {
        $teams = Team::with(['owner', 'players'])
            ->orderBy('wins', 'desc')
            ->orderBy('total_matches', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => TeamResource::collection($teams),
        ]);
    }

    /**
     * Детальная информация о команде
     */
    public function show(int $id): JsonResponse
    {
        $team = Team::with(['owner', 'players', 'players.favoriteMap'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new TeamResource($team),
        ]);
    }

    /**
     * Создание команды
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:teams',
            'tag' => 'nullable|string|max:4',
            'description' => 'nullable|string',
        ]);

        $team = Team::create([
            'name' => $validated['name'],
            'tag' => $validated['tag'] ?? null,
            'description' => $validated['description'] ?? null,
            'owner_id' => $request->user()->id,
            'wins' => 0,
            'losses' => 0,
            'total_matches' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Команда создана',
            'data' => new TeamResource($team),
        ], 201);
    }

    /**
     * Список игроков команды
     */
    public function players(int $id): JsonResponse
    {
        $team = Team::with('players')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $team->players->map(fn($player) => [
                'id' => $player->id,
                'name' => $player->name,
                'aim' => $player->aim,
                'skill' => $player->skill,
                'movement' => $player->movement,
                'rating' => $player->overallRating,
                'primary_stat' => $player->primaryStat,
                'kd_ratio' => $player->kdRatio,
                'kills' => $player->kills,
                'deaths' => $player->deaths,
                'matches_played' => $player->matches_played,
                'favorite_map' => $player->favoriteMap?->display_name,
                'favorite_weapons' => $player->favorite_weapons,
            ]),
        ]);
    }
}