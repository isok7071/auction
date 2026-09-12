<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Domain\Cars\Models\Car;
use App\Domain\Cars\Models\CarPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class VoteEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_accepts_a_vote_after_fetching_a_pair(): void
    {
        $car = Car::factory()->create([
            Car::FIELD_MAKE  => 'FORD',
            Car::FIELD_MODEL => 'MUSTANG',
        ]);
        CarPhoto::factory()->count(2)->for($car)->create();

        $pairResponse = $this->getJson('/voting/pair?model=FORD%20MUSTANG')->assertOk();
        $left = $pairResponse->json('data.left.id');
        $right = $pairResponse->json('data.right.id');

        $this->postJson('/voting/votes', [
            'model'           => 'FORD MUSTANG',
            'winner_photo_id' => $left,
            'loser_photo_id'  => $right,
        ])->assertCreated()
            ->assertJsonStructure(['data' => ['id', 'winner_photo_id', 'loser_photo_id']]);
    }

    public function test_it_rejects_a_repeated_submission_of_an_consumed_pair(): void
    {
        $car = Car::factory()->create([
            Car::FIELD_MAKE  => 'FORD',
            Car::FIELD_MODEL => 'MUSTANG',
        ]);
        CarPhoto::factory()->count(2)->for($car)->create();
        $pair = $this->getJson('/voting/pair?model=FORD%20MUSTANG')->json('data');
        $payload = [
            'model'           => 'FORD MUSTANG',
            'winner_photo_id' => $pair['left']['id'],
            'loser_photo_id'  => $pair['right']['id'],
        ];

        $this->postJson('/voting/votes', $payload)->assertCreated();
        $this->postJson('/voting/votes', $payload)->assertUnprocessable();

        $this->assertDatabaseCount('votes', 1);
    }

    public function test_it_rejects_duplicate_or_tampered_photo_ids(): void
    {
        $car = Car::factory()->create([
            Car::FIELD_MAKE  => 'FORD',
            Car::FIELD_MODEL => 'MUSTANG',
        ]);
        $photos = CarPhoto::factory()->count(3)->for($car)->create();
        $pair = $this->getJson('/voting/pair?model=FORD%20MUSTANG')->json('data');

        $this->postJson('/voting/votes', [
            'model'           => 'FORD MUSTANG',
            'winner_photo_id' => $pair['left']['id'],
            'loser_photo_id'  => $pair['left']['id'],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['loser_photo_id']);

        $unissuedPhotoId = null;

        foreach ($photos as $photo) {
            $photoId = $photo->getKey();

            if (!in_array($photoId, [$pair['left']['id'], $pair['right']['id']], true)) {
                $unissuedPhotoId = $photoId;

                break;
            }
        }

        $this->assertNotNull($unissuedPhotoId);

        $this->postJson('/voting/votes', [
            'model'           => 'FORD MUSTANG',
            'winner_photo_id' => $pair['left']['id'],
            'loser_photo_id'  => $unissuedPhotoId,
        ])->assertUnprocessable();

        $this->assertDatabaseCount('votes', 0);
    }
}
