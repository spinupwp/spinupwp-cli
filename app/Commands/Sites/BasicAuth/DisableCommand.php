<?php

namespace App\Commands\Sites\BasicAuth;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasSiteIdParameter;

class DisableCommand extends BaseCommand
{
    use HasSiteIdParameter;

    protected $signature = 'sites:basic-auth:disable
                            {site_id? : The site to disable basic auth for}
                            {--f|force : Disable basic auth without prompting for confirmation}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'Disable HTTP basic authentication for a site';

    protected function action(): int
    {
        $site = $this->selectSite('Which site would you like to disable basic auth for', fn ($site) => $site->basic_auth['enabled'], 'There are no sites with basic auth enabled.');

        if (is_null($site) || !$this->forceOrConfirm("Are you sure you want to disable basic auth for \"{$site->domain}\"?")) {
            return self::SUCCESS;
        }

        $eventId = $site->disableBasicAuth();

        $this->displaySiteEvent($site, $eventId, 'Basic auth queued for disabling.');

        return self::SUCCESS;
    }
}
