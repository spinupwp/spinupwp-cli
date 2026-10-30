<?php

namespace App\Commands\Sites\Https;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasSiteIdParameter;

class DisableCommand extends BaseCommand
{
    use HasSiteIdParameter;

    protected $signature = 'sites:https:disable
                            {site_id? : The site to disable HTTPS for}
                            {--f|force : Disable HTTPS without prompting for confirmation}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'Disable HTTPS for a site';

    protected function action(): int
    {
        $site = $this->selectSite('Which site would you like to disable HTTPS for', fn ($site) => !empty($site->https['enabled']), 'There are no sites with HTTPS enabled.');

        if (is_null($site) || !$this->forceOrConfirm("Are you sure you want to disable HTTPS for \"{$site->domain}\"?", false)) {
            return self::SUCCESS;
        }

        $eventId = $site->disableHttps();

        $this->displaySiteEvent($site, $eventId, 'HTTPS queued for disabling.');

        return self::SUCCESS;
    }
}
