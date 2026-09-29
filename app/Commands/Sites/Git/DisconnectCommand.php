<?php

namespace App\Commands\Sites\Git;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasSiteIdParameter;

class DisconnectCommand extends BaseCommand
{
    use HasSiteIdParameter;

    protected $signature = 'sites:git:disconnect
                            {site_id? : The site to disconnect Git from}
                            {--f|force : Disconnect Git without prompting for confirmation}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'Disconnect Git from a site';

    protected function action(): int
    {
        $site = $this->selectSite('Which site would you like to disconnect Git from', fn ($site) => $site->git['enabled'], 'There are no sites with Git enabled.');

        if (is_null($site) || !$this->forceOrConfirm("Are you sure you want to disconnect Git from \"{$site->domain}\"?", false)) {
            return self::SUCCESS;
        }

        $eventId = $site->disconnectGit();

        $this->displaySiteEvent($site, $eventId, 'Git queued for disconnection.');

        return self::SUCCESS;
    }
}
