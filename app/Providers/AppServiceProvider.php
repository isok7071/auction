<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Cars\Contracts\CarPhotoStorage;
use App\Domain\Cars\Contracts\CarRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentCarRepository;
use App\Infrastructure\Storage\PublicDiskCarPhotoStorage;
use Illuminate\Database\Eloquent\Model;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void {}
}
