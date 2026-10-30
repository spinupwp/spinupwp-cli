<?php

namespace App\Commands\Sites\Subdomain;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasSiteIdParameter;

class EnableCommand extends BaseCommand
{
    use HasSiteIdParameter;

    protected $signature = 'sites:subdomain:enable
                            {site_id? : The site to enable the SpinupWP subdomain for}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'Enable the SpinupWP subdomain for a site';

    protected function action(): int
    {
        $site = $this->selectSite('Which site would you like to enable the SpinupWP subdomain for', fn ($site) => empty($site->subdomain['enabled']), 'There are no sites with the SpinupWP subdomain disabled.');

        if (is_null($site)) {
            return self::SUCCESS;
        }

        $eventId = $site->enableSpinupwpSubdomain();

        $this->displaySiteEvent($site, $eventId, 'SpinupWP subdomain queued for enabling.');

        return self::SUCCESS;
    }
}
