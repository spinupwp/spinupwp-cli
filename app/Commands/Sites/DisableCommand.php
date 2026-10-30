<?php

namespace App\Commands\Sites;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasSiteIdParameter;

class DisableCommand extends BaseCommand
{
    use HasSiteIdParameter;

    protected $signature = 'sites:disable
                            {site_id? : The site to disable}
                            {--f|force : Disable the site without prompting for confirmation}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'Disable a site so it no longer serves traffic';

    protected function action(): int
    {
        $site = $this->selectSite('Which site would you like to disable', fn ($site) => empty($site->disabled_at), 'There are no enabled sites.');

        if (is_null($site) || !$this->forceOrConfirm("Are you sure you want to disable \"{$site->domain}\"? It will stop serving traffic.", false)) {
            return self::SUCCESS;
        }

        $eventId = $site->disable();

        $this->displaySiteEvent($site, $eventId, 'Site queued for disabling.');

        return self::SUCCESS;
    }
}
