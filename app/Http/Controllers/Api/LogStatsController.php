<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LogStatsRequest;
use App\Services\LogStatsService;
use Illuminate\Http\JsonResponse;

class LogStatsController extends Controller
{
    public function __invoke(LogStatsRequest $request, LogStatsService $logStatsService): JsonResponse
    {
        $validated = $request->validated();

        return response()->json([
            'data' => $logStatsService->analyze(
                $validated['log_text'],
                $validated['keyword_groups'],
            ),
        ]);
    }
}
