<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\VotingModelResource;
use App\Services\VotingPairService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class VotingModelController extends Controller
{
    public function index(VotingPairService $service): AnonymousResourceCollection
    {
        return VotingModelResource::collection($service->models());
    }
}
