<?php

declare(strict_types=1);

namespace App\Domain\Voting\Models;

use App\Domain\Cars\Models\Car;
use App\Domain\Cars\Models\CarPhoto;
use Database\Factories\VoteFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UseFactory(VoteFactory::class)]
final class Vote extends Model
{
    /** @use HasFactory<VoteFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['session_id', 'winner_photo_id', 'loser_photo_id', 'winner_car_id', 'loser_car_id'];

    public function winnerPhoto(): BelongsTo
    {
        return $this->belongsTo(CarPhoto::class, 'winner_photo_id');
    }

    public function loserPhoto(): BelongsTo
    {
        return $this->belongsTo(CarPhoto::class, 'loser_photo_id');
    }

    public function winnerCar(): BelongsTo
    {
        return $this->belongsTo(Car::class, 'winner_car_id');
    }

    public function loserCar(): BelongsTo
    {
        return $this->belongsTo(Car::class, 'loser_car_id');
    }
}
