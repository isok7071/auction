<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Domain\Cars\Models\Car;
use App\Domain\Cars\Models\CarPhoto;
use App\Services\VotingPairService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\Store;
use Tests\TestCase;

final class VotingPairServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_issues_every_photo_before_repeating_at_the_odd_cycle_boundary(): void
    {
        $car = Car::factory()->create([
            Car::FIELD_MAKE  => 'FORD',
            Car::FIELD_MODEL => 'MUSTANG',
        ]);
        $photos = CarPhoto::factory()->count(5)->for($car)->create();
        $session = $this->sessionStore();
        $service = app(VotingPairService::class);

        $first = $service->pairFor('FORD MUSTANG', $session);
        $service->clearPendingPair('FORD MUSTANG', $session);
        $second = $service->pairFor('FORD MUSTANG', $session);
        $service->clearPendingPair('FORD MUSTANG', $session);
        $third = $service->pairFor('FORD MUSTANG', $session);

        $firstIds = [$first->left->getKey(), $first->right->getKey()];
        $secondIds = [$second->left->getKey(), $second->right->getKey()];
        $thirdIds = [$third->left->getKey(), $third->right->getKey()];

        $firstCycleIds = [...$firstIds, ...$secondIds];
        $unseenIds = array_values(array_diff($photos->modelKeys(), $firstCycleIds));

        $this->assertCount(2, array_unique($firstIds));
        $this->assertCount(2, array_unique($secondIds));
        $this->assertCount(4, array_unique($firstCycleIds));
        $this->assertCount(1, $unseenIds);
        $this->assertContains($unseenIds[0], $thirdIds);
        $this->assertCount(1, array_intersect($thirdIds, $firstCycleIds));
    }

    public function test_it_returns_the_pending_pair_until_it_is_cleared(): void
    {
        $car = Car::factory()->create([
            Car::FIELD_MAKE  => 'FORD',
            Car::FIELD_MODEL => 'MUSTANG',
        ]);
        CarPhoto::factory()->count(2)->for($car)->create();
        $session = $this->sessionStore();
        $service = app(VotingPairService::class);

        $first = $service->pairFor('FORD MUSTANG', $session);
        $again = $service->pairFor('FORD MUSTANG', $session);

        $this->assertSame(
            [$first->left->getKey(), $first->right->getKey()],
            [$again->left->getKey(), $again->right->getKey()],
        );
    }

    public function test_it_returns_null_when_a_model_has_fewer_than_two_photos(): void
    {
        $car = Car::factory()->create([
            Car::FIELD_MAKE  => 'FORD',
            Car::FIELD_MODEL => 'MUSTANG',
        ]);
        CarPhoto::factory()->for($car)->create();

        $this->assertNull(app(VotingPairService::class)->pairFor('FORD MUSTANG', $this->sessionStore()));
    }

    private function sessionStore(): Store
    {
        $session = app('session')->driver('array');
        $session->setId('voting-test-session');

        return $session;
    }
}
