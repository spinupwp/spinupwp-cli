<?php

namespace App\Commands\Servers;

use App\Commands\BaseCommand;

class RebootCommand extends BaseCommand
{
    protected $signature = 'servers:reboot
                            {server_id? : The server to reboot}
                            {--all : Reboot all servers}
                            {--f|force : Force reboot}
                            {--profile=}';

    protected $description = 'Reboot a server';

    public function action(): int
    {
        if ((bool) $this->option('all')) {
            $this->rebootServers($this->spinupwp->servers->list()->toArray());
        }

        $serverId = $this->argument('server_id');

        if (empty($serverId)) {
            $serverId = $this->askToSelectServer('Which server would you like to reboot');
        }

        $server = $this->spinupwp->servers->get((int) $serverId);

        $this->rebootServers([$server]);

        return self::SUCCESS;
    }

    protected function rebootServers(array $servers): void
    {
        if (empty($servers)) {
            return;
        }

        foreach ($servers as $server) {
            $response = $server->reboot();
            $this->info("Rebooting server {$server->name}. Event ID: {$response}");
        }
    }
}
