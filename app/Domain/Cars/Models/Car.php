<?php

declare(strict_types=1);

namespace App\Domain\Cars\Models;

use App\Domain\Voting\Models\Vote;
use Database\Factories\CarFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UseFactory(CarFactory::class)]
final class Car extends Model
{
    /** @use HasFactory<CarFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['source_auction_item_id', 'auction_id', 'make', 'model', 'year', 'odometer', 'units', 'engine', 'transmission', 'color', 'source_payload'];

    public function photos(): HasMany
    {
        return $this->hasMany(CarPhoto::class);
    }

    public function wonVotes(): HasMany
    {
        return $this->hasMany(Vote::class, 'winner_car_id');
    }

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'odometer' => 'integer',
            'source_payload' => 'array',
        ];
    }
}
