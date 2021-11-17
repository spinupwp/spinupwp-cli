<?php

namespace App\Commands\Sites;

use App\Commands\BaseCommand;
use App\Commands\Concerns\InteractsWithIO;

class SshCommand extends BaseCommand
{
    use InteractsWithIO;

    protected $signature = 'sites:ssh {site_id?} {--profile=}';

    protected $description = 'Start an SSH session as the site user';

    protected function action(): int
    {
        $siteId = $this->argument('site_id');

        if (empty($siteId)) {
            $siteId = $this->askToSelectSite('Which site would you like to start an SSH session for');
        }

        $site = $this->spinupwp->sites->get((int)$siteId);

        return self::SUCCESS;
    }
}
