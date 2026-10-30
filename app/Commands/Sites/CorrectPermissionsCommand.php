<?php

namespace App\Commands\Sites;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasSiteIdParameter;

class CorrectPermissionsCommand extends BaseCommand
{
    use HasSiteIdParameter;

    protected $signature = 'sites:correct-permissions
                            {site_id? : The site to correct file permissions for}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = "Reset a site's file permissions so the site user owns its files and folders";

    protected function action(): int
    {
        $site = $this->selectSite('Which site would you like to correct file permissions for');

        if (is_null($site)) {
            return self::SUCCESS;
        }

        $eventId = $site->correctFilePermissions();

        $this->displaySiteEvent($site, $eventId, 'File permissions queued for correction.');

        return self::SUCCESS;
    }
}
