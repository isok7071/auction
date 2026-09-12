<?php

declare(strict_types=1);

namespace App\Domain\Cars\Contracts;

use App\Domain\Cars\Data\ImportSourceDto;

interface CarImportSourceDownloader
{
    public function download(string $url): ImportSourceDto;

    public function cleanup(ImportSourceDto $source): void;
}
