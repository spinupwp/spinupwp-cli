<?php

namespace App\Commands\Servers;

use App\Commands\BaseCommand;
use Illuminate\Support\Collection;

class RebootCommand extends BaseCommand
{
    protected $signature = 'servers:reboot
                            {server_id? : The server to reboot}
                            {--all : Reboot all servers}
                            {--f|force : Reboot the server without prompting for confirmation}
                            {--profile=}';

    protected $description = 'Reboot a server';

    public function action(): int
    {
        $servers = collect();

        if ((bool) $this->option('all')) {
            if ($this->forceOrConfirm('Are you sure you want to reboot all servers?')) {
                $servers = $this->spinupwp->listServers();
            }
        } else {
            $server = $this->selectServer('reboot');

            if ($this->forceOrConfirm("Are you sure you want to reboot \"{$server->name}\"?")) {
                $servers = collect([$server]);
            }
        }

        $this->queueResources($servers, 'reboot', 'reboot');

        return self::SUCCESS;
    }
}
