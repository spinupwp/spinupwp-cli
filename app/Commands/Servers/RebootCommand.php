<?php

namespace App\Commands\Servers;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasServerIdParameter;

class RebootCommand extends BaseCommand
{
    use HasServerIdParameter;

    protected $signature = 'servers:reboot
                            {server_id? : The server to reboot}
                            {--all : Reboot all servers}
                            {--f|force : Reboot the server without prompting for confirmation}
                            {--profile=}';

    protected $description = 'Reboot a server';

    public function action(): int
    {
        $servers = collect();

        if ($this->option('all')) {
            if ($this->forceOrConfirm('Are you sure you want to reboot all servers?')) {
                $servers = $this->spinupwp->listServers();
            }
        } else {
            $servers = $this->selectServer('reboot');
        }

        $this->queueResources($servers, 'reboot', 'reboot');

        return self::SUCCESS;
    }
}
