<?php

namespace App\Http\Controllers\Api;

use App\Domains\Game\Models\Tournament;
use App\Domains\Game\Services\TournamentService;
use App\Domains\Game\Enums\TournamentType;
use App\Http\Controllers\Controller;
use App\Http\Resources\TournamentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class TournamentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum', except: ['index', 'show', 'global']),
            new Middleware('throttle:60,1'),
        ];
    }

    /**
     * Список турниров
     */
    public function index(): JsonResponse
    {
        $tournaments = Tournament::with(['matches'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => TournamentResource::collection($tournaments),
        ]);
    }

    /**
     * Детальная информация о турнире
     */
    public function show(int $id): JsonResponse
    {
        $tournament = Tournament::with(['matches.team1', 'matches.team2', 'matches.winner'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new TournamentResource($tournament),
        ]);
    }

    /**
     * Создание турнира
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rules' => 'nullable|array',
            'started_at' => 'nullable|date',
        ]);

        $tournament = Tournament::create([
            'name' => $validated['name'],
            'type' => TournamentType::CUSTOM,
            'rules' => $validated['rules'] ?? null,
            'is_active' => true,
            'started_at' => $validated['started_at'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Турнир создан',
            'data' => new TournamentResource($tournament),
        ], 201);
    }

    /**
     * Глобальный чемпионат
     */
    public function global(): JsonResponse
    {
        $tournament = Tournament::with(['matches.team1', 'matches.team2', 'matches.winner'])
            ->where('type', TournamentType::GLOBAL)
            ->first();

        if (!$tournament) {
            return response()->json([
                'success' => false,
                'message' => 'Глобальный чемпионат не найден',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new TournamentResource($tournament),
        ]);
    }

    /**
     * Генерация расписания турнира
     */
    public function generateSchedule(int $id, Request $request, TournamentService $service): JsonResponse
    {
        $tournament = Tournament::findOrFail($id);

        $days = $request->input('days', 7);

        try {
            $service->generateSchedule($tournament, $days);

            return response()->json([
                'success' => true,
                'message' => "Расписание на {$days} дней сгенерировано",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка генерации: ' . $e->getMessage(),
            ], 500);
        }
    }
}
