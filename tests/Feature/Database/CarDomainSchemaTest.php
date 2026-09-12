<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Domain\Cars\Models\Car;
use App\Domain\Cars\Models\CarPhoto;
use App\Domain\Voting\Models\Vote;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class CarDomainSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_car_photo_and_vote_relationships_persist(): void
    {
        $winnerCar = Car::factory()->create([Car::FIELD_SOURCE_AUCTION_ITEM_ID => '342154']);
        $loserCar = Car::factory()->create([Car::FIELD_SOURCE_AUCTION_ITEM_ID => '279207']);
        $winnerPhoto = CarPhoto::factory()->for($winnerCar)->create([
            CarPhoto::FIELD_SOURCE_FILENAME => '71b32420fd9ba92739b3e06704f7b213.jpg',
        ]);
        $loserPhoto = CarPhoto::factory()->for($loserCar)->create([
            CarPhoto::FIELD_SOURCE_FILENAME => '48d86a78c394dc68cea18117ec223ba4.jpg',
        ]);

        $vote = Vote::factory()->create([
            Vote::FIELD_WINNER_PHOTO_ID => $winnerPhoto->getKey(),
            Vote::FIELD_LOSER_PHOTO_ID  => $loserPhoto->getKey(),
            Vote::FIELD_WINNER_CAR_ID   => $winnerCar->getKey(),
            Vote::FIELD_LOSER_CAR_ID    => $loserCar->getKey(),
        ]);

        $winnerCar->load(['photos', 'wonVotes']);

        $this->assertModelExists($winnerCar);
        $this->assertTrue($winnerCar->photos->contains($winnerPhoto));
        $this->assertTrue($winnerCar->wonVotes->contains($vote));
    }

    public function test_cars_table_contains_only_normalized_source_data(): void
    {
        $this->assertFalse(Schema::hasColumn('cars', 'source_payload'));
    }

    public function test_car_photo_exposes_public_storage_url(): void
    {
        Storage::fake('public');
        $photo = CarPhoto::factory()->make([
            CarPhoto::FIELD_STORAGE_PATH => 'cars/example.jpg',
        ]);

        $this->assertSame(Storage::disk('public')->url('cars/example.jpg'), $photo->url());
    }

    public function test_source_auction_item_id_is_unique(): void
    {
        Car::factory()->create([Car::FIELD_SOURCE_AUCTION_ITEM_ID => '342154']);

        $this->expectException(QueryException::class);

        Car::factory()->create([Car::FIELD_SOURCE_AUCTION_ITEM_ID => '342154']);
    }

    public function test_source_photo_filename_is_unique(): void
    {
        CarPhoto::factory()->create([CarPhoto::FIELD_SOURCE_FILENAME => 'example.jpg']);

        $this->expectException(QueryException::class);

        CarPhoto::factory()->create([CarPhoto::FIELD_SOURCE_FILENAME => 'example.jpg']);
    }

    public function test_vote_factory_links_each_photo_to_its_recorded_car(): void
    {
        $vote = Vote::factory()->create();
        $vote->load(['winnerPhoto', 'loserPhoto']);

        $this->assertSame(
            $vote->getAttribute(Vote::FIELD_WINNER_CAR_ID),
            $vote->winnerPhoto->getAttribute(CarPhoto::FIELD_CAR_ID),
        );
        $this->assertSame(
            $vote->getAttribute(Vote::FIELD_LOSER_CAR_ID),
            $vote->loserPhoto->getAttribute(CarPhoto::FIELD_CAR_ID),
        );
    }
}
