<?php

namespace App\Commands\Sites;

use App\Commands\BaseCommand;
use Exception;

class GitDeployCommand extends BaseCommand
{
    protected $signature = 'sites:deploy
                            {site_id? : The site to deploy}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'Run a Git deployment';

    public function action(): int
    {
        $siteId = $this->argument('site_id');

        if (empty($siteId)) {
            $siteId = $this->askToSelectSite('Which site would you like to deploy', fn ($site) => $site->git['enabled']);
        }

        $site = $this->spinupwp->sites->get((int) $siteId);

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

        $this->successfulStep('Site queued for deployment.');

        $this->stepTable([
            'Event ID',
            'Site',
        ], [[
            $eventId,
            $site->domain,
        ]]);

        return self::SUCCESS;
    }
}
