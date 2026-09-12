<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Domain\Cars\Models\Car;
use App\Domain\Cars\Models\CarPhoto;
use App\Domain\Voting\Models\Vote;
use App\Services\VoteService;
use App\Services\VotingPairService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\Store;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class VoteServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_an_issued_pair_and_clears_pending_state(): void
    {
        $car = Car::factory()->create([
            Car::FIELD_MAKE  => 'FORD',
            Car::FIELD_MODEL => 'MUSTANG',
        ]);
        CarPhoto::factory()->count(2)->for($car)->create();
        $session = $this->sessionStore();
        $pairService = app(VotingPairService::class);
        $pair = $pairService->pairFor('FORD MUSTANG', $session);

        $vote = app(VoteService::class)->record(
            'FORD MUSTANG',
            $pair->left->getKey(),
            $pair->right->getKey(),
            $session,
        );

        $this->assertInstanceOf(Vote::class, $vote);
        $this->assertDatabaseCount('votes', 1);
        $this->assertSame($pair->left->getKey(), $vote->getAttribute(Vote::FIELD_WINNER_PHOTO_ID));
        $this->assertFalse($session->has('voting.pending_pairs.' . sha1('FORD MUSTANG')));
    }

    public function test_it_rejects_a_pair_that_is_not_pending(): void
    {
        $session = $this->sessionStore();

        $this->expectException(ValidationException::class);

        app(VoteService::class)->record('FORD MUSTANG', 1, 2, $session);
    }

    public function test_it_rejects_duplicate_photo_ids_without_writing_a_vote(): void
    {
        $session = $this->sessionStore();

        try {
            app(VoteService::class)->record('FORD MUSTANG', 1, 1, $session);
            $this->fail('Expected a validation exception for duplicate photo IDs.');
        } catch (ValidationException) {
            $this->assertDatabaseCount('votes', 0);
        }
    }

    public function test_it_rejects_photos_that_do_not_match_the_issued_pair_without_writing_a_vote(): void
    {
        $car = Car::factory()->create([
            Car::FIELD_MAKE  => 'FORD',
            Car::FIELD_MODEL => 'MUSTANG',
        ]);
        CarPhoto::factory()->count(3)->for($car)->create();
        $session = $this->sessionStore();
        $pair = app(VotingPairService::class)->pairFor('FORD MUSTANG', $session);
        $unissuedPhoto = CarPhoto::query()
            ->whereKeyNot([$pair->left->getKey(), $pair->right->getKey()])
            ->firstOrFail();

        try {
            app(VoteService::class)->record(
                'FORD MUSTANG',
                $pair->left->getKey(),
                $unissuedPhoto->getKey(),
                $session,
            );
            $this->fail('Expected a validation exception for a non-issued photo.');
        } catch (ValidationException) {
            $this->assertDatabaseCount('votes', 0);
        }
    }

    public function test_it_rejects_a_pending_pair_that_does_not_belong_to_the_requested_model(): void
    {
        $otherCar = Car::factory()->create([
            Car::FIELD_MAKE  => 'TOYOTA',
            Car::FIELD_MODEL => 'CAMRY',
        ]);
        $otherPhotos = CarPhoto::factory()->count(2)->for($otherCar)->create();
        $session = $this->sessionStore();
        $session->put('voting.pending_pairs.' . sha1('FORD MUSTANG'), [
            'left'  => $otherPhotos[0]->getKey(),
            'right' => $otherPhotos[1]->getKey(),
        ]);

        try {
            app(VoteService::class)->record(
                'FORD MUSTANG',
                $otherPhotos[0]->getKey(),
                $otherPhotos[1]->getKey(),
                $session,
            );
            $this->fail('Expected a validation exception for cross-model photos.');
        } catch (ValidationException) {
            $this->assertDatabaseCount('votes', 0);
        }
    }

    private function sessionStore(): Store
    {
        $session = app('session')->driver('array');
        $session->setId('vote-test-session');

        return $session;
    }
}
