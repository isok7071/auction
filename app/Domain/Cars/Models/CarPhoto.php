<?php

declare(strict_types=1);

namespace App\Domain\Cars\Models;

use Database\Factories\CarPhotoFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UseFactory(CarPhotoFactory::class)]
final class CarPhoto extends Model
{
    /** @use HasFactory<CarPhotoFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['car_id', 'source_filename', 'storage_path', 'checksum'];

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }
}
