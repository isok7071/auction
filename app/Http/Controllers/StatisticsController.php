<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\IndexStatisticsRequest;
use App\Http\Resources\StatisticsCollection;
use App\Services\StatisticsService;

final class StatisticsController extends Controller
{
    public function index(
        IndexStatisticsRequest $request,
        StatisticsService $service
    ): StatisticsCollection {
        $data = $request->validated();
        $result = $service->forFilters(
            $data['model'] ?? null,
            isset($data['year_from']) ? (int) $data['year_from'] : null,
            isset($data['year_to']) ? (int) $data['year_to'] : null,
        );

        return new StatisticsCollection(
            $result->cars,
            $result->totalVotes
        );
    }
}
