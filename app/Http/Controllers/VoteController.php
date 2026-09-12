<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreVoteRequest;
use App\Http\Resources\VoteResource;
use App\Services\VoteService;
use Illuminate\Http\JsonResponse;

final class VoteController extends Controller
{
    public function store(StoreVoteRequest $request, VoteService $service): JsonResponse
    {
        $data = $request->validated();
        $vote = $service->record(
            (string) $data['model'],
            (int) $data['winner_photo_id'],
            (int) $data['loser_photo_id'],
            $request->session(),
        );

        return (new VoteResource($vote))->response()->setStatusCode(201);
    }
}
