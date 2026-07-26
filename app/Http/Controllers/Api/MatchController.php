<?php

namespace App\Http\Controllers\Api;

use App\Domains\Game\Models\MatchModel;
use App\Domains\Game\Models\Team;
use App\Domains\Game\Services\MatchService;
use App\Http\Controllers\Controller;
use App\Http\Resources\MatchResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Redis;

class MatchController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum', except: ['index', 'show', 'progress']),
            new Middleware('throttle:60,1'),
        ];
    }

    /**
     * Список завершенных матчей
     */
    public function index(): JsonResponse
    {
        $matches = MatchModel::with(['team1', 'team2', 'winner'])
            ->where('status', 'finished')
            ->orderBy('finished_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => MatchResource::collection($matches),
        ]);
    }

    /**
     * Детальная информация о матче
     */
    public function show(int $id): JsonResponse
    {
        $match = MatchModel::with([
            'team1.players',
            'team2.players',
            'events' => function ($query) {
                $query->with(['location', 'kills.killer', 'kills.victim']);
            },
            'map1',
            'map2',
            'winner',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new MatchResource($match),
        ]);
    }

    /**
     * Создание товарищеского матча
     */
    public function startFriendly(Request $request, MatchService $matchService): JsonResponse
    {
        $validated = $request->validate([
            'team1_id' => 'required|exists:teams,id',
            'team2_id' => 'required|exists:teams,id|different:team1_id',
            'total_events' => 'sometimes|integer|min:4|max:100',
        ]);

        // Проверяем, что в командах есть игроки
        $team1 = Team::with('players')->findOrFail($validated['team1_id']);
        $team2 = Team::with('players')->findOrFail($validated['team2_id']);

        if ($team1->players->count() < 2 || $team2->players->count() < 2) {
            return response()->json([
                'success' => false,
                'message' => 'В каждой команде должно быть минимум 2 игрока',
            ], 422);
        }

        try {
            $match = $matchService->createFriendlyMatch(
                $validated['team1_id'],
                $validated['team2_id'],
                $validated['total_events'] ?? 40
            );

            $matchService->startMatch($match);

            return response()->json([
                'success' => true,
                'message' => 'Товарищеский матч создан и запущен',
                'data' => [
                    'match_id' => $match->id,
                    'team1' => $team1->name,
                    'team2' => $team2->name,
                    'status' => $match->status->value,
                    'total_events' => $match->total_events,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при создании матча: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Получение прогресса матча
     */
    public function progress(int $id): JsonResponse
    {
        $match = MatchModel::findOrFail($id);

        try {
            $progress = Redis::get("match:{$id}:progress") ?? 0;
            $score = Redis::get("match:{$id}:score") ?? "{$match->score_team1}:{$match->score_team2}";
            $currentEvent = Redis::get("match:{$id}:current_event") ?? $match->current_event;
            $totalEvents = Redis::get("match:{$id}:total_events") ?? $match->total_events;
        } catch (\Exception $e) {
            $progress = $match->progress();
            $score = "{$match->score_team1}:{$match->score_team2}";
            $currentEvent = $match->current_event;
            $totalEvents = $match->total_events;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'match_id' => $id,
                'status' => $match->status->value,
                'progress' => (float) $progress,
                'current_event' => (int) $currentEvent,
                'total_events' => (int) $totalEvents,
                'score' => $score,
                'is_finished' => $match->isFinished(),
                'is_in_progress' => $match->isInProgress(),
            ],
        ]);
    }

    /**
     * Активные матчи
     */
    public function active(): JsonResponse
    {
        $matches = MatchModel::with(['team1', 'team2'])
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => MatchResource::collection($matches),
        ]);
    }
}
