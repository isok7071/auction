<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Domain\Cars\Models\Car;
use App\Domain\Cars\Models\CarPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

final class VoteEndpointTest extends TestCase
{
    use RefreshDatabase;

    private const string CSRF_TOKEN = 'vote-endpoint-test-token';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withSession(['_token' => self::CSRF_TOKEN]);
    }

    public function test_it_creates_a_vote_for_an_issued_pair_and_returns_its_photo_ids(): void
    {
        $car = Car::factory()->create([
            Car::FIELD_MAKE  => 'FORD',
            Car::FIELD_MODEL => 'MUSTANG',
        ]);
        CarPhoto::factory()->count(2)->for($car)->create();

        $pairResponse = $this->getJson('/voting/pair?model=FORD%20MUSTANG')->assertOk();
        $left = $pairResponse->json('data.left.id');
        $right = $pairResponse->json('data.right.id');

        $this->postVote([
            'model'           => 'FORD MUSTANG',
            'winner_photo_id' => $left,
            'loser_photo_id'  => $right,
        ])->assertCreated()
            ->assertJsonPath('data.winner_photo_id', $left)
            ->assertJsonPath('data.loser_photo_id', $right);

        $this->assertDatabaseHas('votes', [
            'winner_photo_id' => $left,
            'loser_photo_id'  => $right,
        ]);
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

        $this->postVote($payload)->assertCreated();
        $this->postVote($payload)->assertUnprocessable();

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

        $this->postVote([
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

        $this->postVote([
            'model'           => 'FORD MUSTANG',
            'winner_photo_id' => $pair['left']['id'],
            'loser_photo_id'  => $unissuedPhotoId,
        ])->assertUnprocessable();

        $this->assertDatabaseCount('votes', 0);
    }

    public function test_it_rate_limits_vote_submissions_per_ip_address(): void
    {
        for ($attempt = 1; $attempt <= 30; $attempt++) {
            $this->postVote([])->assertUnprocessable();
        }

        $this->postVote([])->assertTooManyRequests();
    }

    /**
     * @param  array{model: string, winner_photo_id: int, loser_photo_id: int}  $payload
     */
    private function postVote(array $payload): TestResponse
    {
        return $this->withHeader('X-CSRF-TOKEN', self::CSRF_TOKEN)
            ->postJson('/voting/votes', $payload);
    }
}
