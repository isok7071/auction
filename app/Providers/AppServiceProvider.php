<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Cars\Contracts\CarPhotoStorage;
use App\Domain\Cars\Contracts\CarRepository;
use App\Domain\Statistics\Contracts\StatisticsRepository;
use App\Domain\Voting\Contracts\VoteRepository;
use App\Domain\Voting\Contracts\VotingRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentCarRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentStatisticsRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentVoteRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentVotingRepository;
use App\Infrastructure\Storage\PublicDiskCarPhotoStorage;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CarRepository::class, EloquentCarRepository::class);
        $this->app->bind(CarPhotoStorage::class, PublicDiskCarPhotoStorage::class);
        $this->app->bind(VotingRepository::class, EloquentVotingRepository::class);
        $this->app->bind(VoteRepository::class, EloquentVoteRepository::class);
        $this->app->bind(StatisticsRepository::class, EloquentStatisticsRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('voting', function (Request $request): Limit {
            return Limit::perMinute(30)->by($request->ip());
        });
    }
}
