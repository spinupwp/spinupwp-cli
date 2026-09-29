<?php

namespace App\Commands\Sites\Backups;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasSiteIdParameter;
use App\Commands\Concerns\ParsesOptions;

class SettingsCommand extends BaseCommand
{
    use HasSiteIdParameter;
    use ParsesOptions;

    protected $signature = 'sites:backups:settings
                            {site_id? : The site to update}
                            {--storage-provider-id= : The ID of a storage provider configured in SpinupWP}
                            {--bucket= : The bucket (or filesystem path for SFTP) to store backups in}
                            {--region= : The region for the storage provider}
                            {--storage-class= : The storage class for AWS S3 storage providers}
                            {--database= : The ID of the database to back up, or "none" to clear it}
                            {--exclude-path=* : A path to exclude from backups (repeatable)}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'Update the backup settings for a site';

    protected array $validationLabels = [
        'storage_provider_bucket' => 'Bucket',
        'storage_provider_region' => 'Region',
        'storage_provider_class'  => 'Storage Class',
    ];

    protected function action(): int
    {
        $payload = $this->withoutNulls([
            'storage_provider_id'     => $this->intOption('storage-provider-id'),
            'storage_provider_bucket' => $this->stringOption('bucket'),
            'storage_provider_region' => $this->stringOption('region'),
            'storage_provider_class'  => $this->stringOption('storage-class'),
            'paths_to_exclude'        => $this->multilineOption('exclude-path'),
        ]);

        if ($this->stringOption('database') === 'none') {
            $payload['database'] = null;
        } elseif (!is_null($database = $this->intOption('database'))) {
            $payload['database'] = $database;
        }

        $this->ensureOptionsProvided($payload);

        $site = $this->selectSite('Which site would you like to update the backup settings for');

        if (is_null($site)) {
            return self::SUCCESS;
        }

        $site->updateBackupSettings($payload);

        $this->successfulStep('Backup settings updated.');

        return self::SUCCESS;
    }
}
