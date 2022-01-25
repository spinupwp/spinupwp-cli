<?php

namespace App\Commands\Services;

use App\Commands\BaseCommand;
use App\Commands\Concerns\SelectsServer;

class NginxCommand extends BaseCommand
{
    use SelectsServer;

    protected $signature = 'services:nginx
                            {server_id? : The server id}
                            {--all : Restart Nginx on all servers}
                            {--f|force : Restart Nginx without prompting for confirmation}
                            {--profile=}';

    protected $description = 'Restart Nginx';

    public function action(): int
    {
        if ($this->option('all') && $this->forceOrConfirm('Are you sure you want to restart Nginx on all servers?')) {
            $servers = $this->spinupwp->listServers();
        } else {
            $servers = $this->selectServer('restart Nginx on');
        }

        $this->queueResources($servers, 'restartNginx', 'Nginx restart');

        return self::SUCCESS;
    }
}
