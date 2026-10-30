<?php

namespace App\Commands\Sites;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasSiteIdParameter;
use Exception;

class GitDeployCommand extends BaseCommand
{
    use HasSiteIdParameter;

    protected $signature = 'sites:deploy
                            {site_id? : The site to deploy}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'Run a Git deployment';

    public function action(): int
    {
        $site = $this->selectSite('Which site would you like to deploy', fn ($site) => $site->git['enabled'], 'There are no sites with Git enabled.');

        if (is_null($site)) {
            return self::SUCCESS;
        }

        if (!$site->git['enabled']) {
            $this->warn('This site is not configured for Git deployments.');
            return self::SUCCESS;
        }

        try {
            $eventId = $site->gitDeploy();
        } catch (Exception $e) {
            $this->warn('The site is already being deployed.');
            return self::SUCCESS;
        }

        $this->displaySiteEvent($site, $eventId, 'Site queued for deployment.');

        return self::SUCCESS;
    }
}
