<?php

namespace App\Commands\Sites;

use App\Commands\BaseCommand;
use App\Commands\Concerns\InteractsWithIO;
use App\Commands\Concerns\InteractsWithRemote;

class SshCommand extends BaseCommand
{
    use InteractsWithIO;
    use InteractsWithRemote;

    protected $signature = 'sites:ssh {site_id?} {--profile=}';

    protected $description = 'Start an SSH session as the site user';

    protected function action(): int
    {
        $siteId = $this->argument('site_id');

        if (empty($siteId)) {
            $siteId = $this->askToSelectSite('Which site would you like to start an SSH session for');
        }

        $site   = $this->spinupwp->sites->get((int)$siteId);
        $server = $this->spinupwp->servers->get($site->server_id);

        $this->line("Establishing a secure connection to [<comment>{$server->name}</comment>] as [<comment>{$site->site_user}</comment>]...");

        return $this->ssh(
            $site->site_user,
            $server->ip_address,
            $server->ssh_port,
        );
    }
}
