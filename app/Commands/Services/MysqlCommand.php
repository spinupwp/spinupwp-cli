<?php

namespace App\Commands\Services;

use App\Commands\BaseCommand;
use App\Commands\Concerns\SelectsServer;

class MysqlCommand extends BaseCommand
{
    use SelectsServer;

    protected $signature = 'services:mysql
                            {server_id? : The server to restart MySQL on}
                            {--all : Restart MySQL on all servers}
                            {--f|force : Restart MySQL without prompting for confirmation}
                            {--profile=}';

    protected $description = 'Restart MySQL';

    public function action(): int
    {
        if ($this->option('all') && $this->forceOrConfirm('Are you sure you want to restart MySQL on all servers?')) {
            $servers = $this->spinupwp->listServers();
        } else {
            $servers = $this->selectServer('restart MySQL on');
        }

        $this->queueResources($servers, 'restartMysql', 'MySQL restart');

        return self::SUCCESS;
    }
}
