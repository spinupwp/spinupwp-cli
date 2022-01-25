<?php

namespace App\Commands\Services;

use App\Commands\BaseCommand;

class PhpCommand extends BaseCommand
{
    protected $signature = 'services:php
                            {server_id? : The server id}
                            {--all : Restart PHP on all servers}
                            {--f|force : Restart PHP without prompting for confirmation}
                            {--profile=}';

    protected $description = 'Restart PHP';

    public function action(): int
    {
        if ($this->option('all') && $this->forceOrConfirm('Are you sure you want to restart PHP on all servers?')) {
            $servers = $this->spinupwp->listServers();
        } else {
            $servers = $this->selectServer('restart PHP on');
        }

        $this->queueResources($servers, 'restartPhp', 'PHP restart');

        return self::SUCCESS;
    }
}
