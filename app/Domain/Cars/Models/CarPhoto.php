<?php

declare(strict_types=1);

namespace App\Domain\Cars\Models;

use Database\Factories\CarPhotoFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $car_id
 * @property string $source_filename
 * @property string $storage_path
 * @property string $checksum
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Car $car
 */
#[UseFactory(CarPhotoFactory::class)]
final class CarPhoto extends Model
{
    /** @use HasFactory<CarPhotoFactory> */
    use HasFactory;

    public const string FIELD_ID = 'id';

    public const string FIELD_CAR_ID = 'car_id';

    public const string FIELD_SOURCE_FILENAME = 'source_filename';

    public const string FIELD_STORAGE_PATH = 'storage_path';

    public const string FIELD_CHECKSUM = 'checksum';

    public const string FIELD_CREATED_AT = 'created_at';

    public const string FIELD_UPDATED_AT = 'updated_at';

    /** @var list<string> */
    protected $fillable = [
        self::FIELD_CAR_ID,
        self::FIELD_SOURCE_FILENAME,
        self::FIELD_STORAGE_PATH,
        self::FIELD_CHECKSUM,
    ];

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class, self::FIELD_CAR_ID);
    }

    public function url(): string
    {
        return Storage::disk('public')->url((string) $this->getAttribute(self::FIELD_STORAGE_PATH));
    }
}
