<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\CarImportService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('cars:import {source? : Local source directory}')]
#[Description('Import auction cars and photos from a local source directory')]
final class ImportCarsCommand extends Command
{
    public function handle(CarImportService $service): int
    {
        $source = $this->argument('source') ?? config('fordewind.import_source_path');

        $result = $service->import((string) $source);

        $this->line(
            "created={$result->createdCars} updated={$result->updatedCars} photos={$result->importedPhotos}"
        );

        return self::SUCCESS;
    }
}
