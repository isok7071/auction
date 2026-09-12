<?php

declare(strict_types=1);

namespace App\Domain\Cars\Data;

use InvalidArgumentException;

final readonly class ImportedCarDto
{
    public function __construct(
        public string $sourceAuctionItemId,
        public ?string $auctionId,
        public string $make,
        public string $model,
        public int $year,
        public ?int $odometer,
        public ?string $units,
        public ?string $engine,
        public ?string $transmission,
        public ?string $color,
        public string $sourceFilename
    ) {}

    /** @param array<string, mixed> $payload */
    public static function fromPayload(array $payload): self
    {
        foreach (['AuctionItemId', 'Make', 'Model', 'Year', 'Image'] as $key) {
            if (!isset($payload[$key]) || $payload[$key] === '') {
                throw new InvalidArgumentException("Missing required source field: {$key}");
            }
        }

        return new self(
            sourceAuctionItemId: (string) $payload['AuctionItemId'],
            auctionId: isset($payload['AuctionId']) ? (string) $payload['AuctionId'] : null,
            make: (string) $payload['Make'],
            model: (string) $payload['Model'],
            year: (int) $payload['Year'],
            odometer: isset($payload['Odometer']) ? (int) $payload['Odometer'] : null,
            units: isset($payload['Units']) ? (string) $payload['Units'] : null,
            engine: isset($payload['Engine']) ? (string) $payload['Engine'] : null,
            transmission: isset($payload['Transmission']) ? (string) $payload['Transmission'] : null,
            color: isset($payload['Color']) ? (string) $payload['Color'] : null,
            sourceFilename: (string) $payload['Image'],
        );
    }
}
