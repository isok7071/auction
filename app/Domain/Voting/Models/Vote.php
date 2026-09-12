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
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $session_id
 * @property int $winner_photo_id
 * @property int $loser_photo_id
 * @property int $winner_car_id
 * @property int $loser_car_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read CarPhoto $winnerPhoto
 * @property-read CarPhoto $loserPhoto
 * @property-read Car $winnerCar
 * @property-read Car $loserCar
 */
#[UseFactory(VoteFactory::class)]
final class Vote extends Model
{
    /** @use HasFactory<VoteFactory> */
    use HasFactory;

    public const string FIELD_ID = 'id';

    public const string FIELD_SESSION_ID = 'session_id';

    public const string FIELD_WINNER_PHOTO_ID = 'winner_photo_id';

    public const string FIELD_LOSER_PHOTO_ID = 'loser_photo_id';

    public const string FIELD_WINNER_CAR_ID = 'winner_car_id';

    public const string FIELD_LOSER_CAR_ID = 'loser_car_id';

    public const string FIELD_CREATED_AT = 'created_at';

    public const string FIELD_UPDATED_AT = 'updated_at';

    /** @var list<string> */
    protected $fillable = [
        self::FIELD_SESSION_ID,
        self::FIELD_WINNER_PHOTO_ID,
        self::FIELD_LOSER_PHOTO_ID,
        self::FIELD_WINNER_CAR_ID,
        self::FIELD_LOSER_CAR_ID,
    ];

    public function winnerPhoto(): BelongsTo
    {
        return $this->belongsTo(CarPhoto::class, self::FIELD_WINNER_PHOTO_ID);
    }

    public function loserPhoto(): BelongsTo
    {
        return $this->belongsTo(CarPhoto::class, self::FIELD_LOSER_PHOTO_ID);
    }

    public function winnerCar(): BelongsTo
    {
        return $this->belongsTo(Car::class, self::FIELD_WINNER_CAR_ID);
    }

    public function loserCar(): BelongsTo
    {
        return $this->belongsTo(Car::class, self::FIELD_LOSER_CAR_ID);
    }
}
