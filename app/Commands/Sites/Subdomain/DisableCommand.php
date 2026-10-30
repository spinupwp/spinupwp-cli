<?php

namespace App\Commands\Sites\Subdomain;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasSiteIdParameter;

class DisableCommand extends BaseCommand
{
    use HasSiteIdParameter;

    protected $signature = 'sites:subdomain:disable
                            {site_id? : The site to disable the SpinupWP subdomain for}
                            {--f|force : Disable the SpinupWP subdomain without prompting for confirmation}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'Disable the SpinupWP subdomain for a site';

    protected function action(): int
    {
        $site = $this->selectSite('Which site would you like to disable the SpinupWP subdomain for', fn ($site) => !empty($site->subdomain['enabled']), 'There are no sites with the SpinupWP subdomain enabled.');

        if (is_null($site) || !$this->forceOrConfirm("Are you sure you want to disable the SpinupWP subdomain for \"{$site->domain}\"?")) {
            return self::SUCCESS;
        }

        $eventId = $site->disableSpinupwpSubdomain();

        $this->displaySiteEvent($site, $eventId, 'SpinupWP subdomain queued for disabling.');

        return self::SUCCESS;
    }
}
