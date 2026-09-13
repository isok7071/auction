<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ShowVotingPairRequest;
use App\Http\Resources\VotingPairResource;
use App\Services\VotingPairService;
use Illuminate\Http\JsonResponse;

final class VotingPairController extends Controller
{
    public function show(ShowVotingPairRequest $request, VotingPairService $service): VotingPairResource|JsonResponse
    {
        $modelKey = (string) $request->validated('model');

        if (!$service->hasModel($modelKey)) {
            return response()->json([
                'message' => 'The selected model is not available.',
                'errors'  => ['model' => ['The selected model is not available.']],
            ], 422);
        }

        $pair = $service->pairFor(
            $modelKey,
            $request->session()
        );

        if ($pair === null) {
            return response()->json([
                'data' => null,
                'meta' => ['reason' => 'not_enough_photos'],
            ]);
        }

        return new VotingPairResource($pair);
    }
}
