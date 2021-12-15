<?php

namespace App\Commands\Servers;

use App\Commands\BaseCommand;

class RebootCommand extends BaseCommand
{
    protected $signature = 'servers:reboot
                            {server_id? : The server to reboot}
                            {--all : Reboot all servers}
                            {--profile=}';

    protected $description = 'Reboot a server';

    public function action(): int
    {
        $serversToDelete = [];

        $serverId = $this->argument('server_id');

        if (empty($serverId)) {
            $serverId = $this->askToSelectServer('Which server would you like to reboot?');
        }

        $server = $this->spinupwp->servers->get($serverId);

        if ($this->confirm("Are you sure you want to reboot \"{$server->name}\"?", 'yes')) {
            $serversToDelete[] = $server;
        }

        if (empty($serversToDelete)) {
            return self::SUCCESS;
        }

        foreach ($serversToDelete as $server) {
            $response = $server->reboot();
            $this->info("Server reboot in progress. Event ID: {$response}");
        }

        return self::SUCCESS;
    }
}
