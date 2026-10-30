<?php

namespace App\Commands\StorageProviders;

use App\Commands\StorageProviders\StorageProviders;

class ListCommand extends StorageProviders
{
    protected $signature = 'storage-providers:list
                            {--fields= : The fields to output}
                            {--format= : The output format (json or table)}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = "List your team's storage providers";

    protected function action(): int
    {
        $storageProviders = $this->spinupwp->listStorageProviders();

        if ($storageProviders->isEmpty()) {
            $this->warn('No storage providers found.');
            return self::SUCCESS;
        }

        if ($this->shouldSpecifyFields()) {
            $this->saveFieldsFilter();
            $storageProviders->transform(fn ($storageProvider) => $this->specifyFields($storageProvider));
            $this->format($storageProviders);
            return self::SUCCESS;
        }

        if ($this->displayFormat() === 'table') {
            $storageProviders->transform(fn ($storageProvider) => $this->specifyFields($storageProvider));
        }

        $this->format($storageProviders);

        return self::SUCCESS;
    }
}
