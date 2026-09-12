<?php

declare(strict_types=1);

namespace App\Infrastructure\Storage;

use App\Domain\Cars\Contracts\CarPhotoStorage;
use App\Domain\Cars\Data\StoredPhotoDto;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

final class PublicDiskCarPhotoStorage implements CarPhotoStorage
{
    public function store(string $sourcePath, string $sourceFilename): StoredPhotoDto
    {
        if (basename($sourceFilename) !== $sourceFilename) {
            throw new InvalidArgumentException('Image filename must not contain a directory path.');
        }

        $imagePath = $sourcePath . DIRECTORY_SEPARATOR . $sourceFilename;

        if (!is_file($imagePath)) {
            throw new InvalidArgumentException("Image file not found: {$sourceFilename}");
        }

        $storagePath = Storage::disk('public')->putFileAs('cars', new File($imagePath), $sourceFilename);

        return new StoredPhotoDto($storagePath, hash_file('sha256', $imagePath));
    }
}
