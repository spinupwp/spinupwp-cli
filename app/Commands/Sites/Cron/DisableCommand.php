<?php

namespace App\Commands\Sites\Cron;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasSiteIdParameter;

class DisableCommand extends BaseCommand
{
    use HasSiteIdParameter;

    protected $signature = 'sites:cron:disable
                            {site_id? : The site to disable the WP cron for}
                            {--f|force : Disable the WP cron without prompting for confirmation}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'Disable the server-level WordPress cron for a site';

    protected function action(): int
    {
        $site = $this->selectSite('Which site would you like to disable the WP cron for', fn ($site) => $site->cron['enabled'], 'There are no sites with WP cron enabled.');

        if (is_null($site) || !$this->forceOrConfirm("Are you sure you want to disable the WP cron for \"{$site->domain}\"?")) {
            return self::SUCCESS;
        }

        $eventId = $site->disableCron();

        $this->displaySiteEvent($site, $eventId, 'WP cron queued for disabling.');

        return self::SUCCESS;
    }
}
