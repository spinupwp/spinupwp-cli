<?php

namespace App\Commands\Sites;

use App\Commands\BaseCommand;

class GitDeployCommand extends BaseCommand
{
    protected $signature = 'sites:deploy
                            {site_id? : The site to deploy}
                            {--profile=}';

    protected $description = 'Deploy a site via Git';

    public function action(): int
    {
        $siteId = $this->argument('site_id');

        if (empty($siteId)) {
            $siteId = $this->askToSelectSite('Which site would you like to deploy?');
        }

        $site = $this->spinupwp->sites->get((int) $siteId);

        $response = $site->gitDeploy();

        $this->line("Deploying site \"{$site->domain}\". Event ID: {$response}.");

        return self::SUCCESS;
    }
}
