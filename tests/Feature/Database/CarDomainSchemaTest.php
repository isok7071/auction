<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Domain\Cars\Models\Car;
use App\Domain\Cars\Models\CarPhoto;
use App\Domain\Voting\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CarDomainSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_car_photo_and_vote_relationships_persist(): void
    {
        $winnerCar = Car::factory()->create(['source_auction_item_id' => '342154']);
        $loserCar = Car::factory()->create(['source_auction_item_id' => '279207']);
        $winnerPhoto = CarPhoto::factory()->for($winnerCar)->create([
            'source_filename' => '71b32420fd9ba92739b3e06704f7b213.jpg',
        ]);
        $loserPhoto = CarPhoto::factory()->for($loserCar)->create([
            'source_filename' => '48d86a78c394dc68cea18117ec223ba4.jpg',
        ]);

        $vote = Vote::factory()->create([
            'winner_photo_id' => $winnerPhoto->id,
            'loser_photo_id' => $loserPhoto->id,
            'winner_car_id' => $winnerCar->id,
            'loser_car_id' => $loserCar->id,
        ]);

        $this->assertModelExists($winnerCar);
        $this->assertTrue($winnerCar->photos->contains($winnerPhoto));
        $this->assertTrue($winnerCar->wonVotes->contains($vote));
    }
}
