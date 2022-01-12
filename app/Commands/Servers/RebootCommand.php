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
        if ((bool) $this->option('all')) {
            $this->rebootServers($this->spinupwp->servers->list()->toArray());
            return self::SUCCESS;
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

        $events = [];

        foreach ($servers as $server) {
            try {
                $eventId = $server->reboot();
                $this->successfulStep('Server queued for reboot.');

                $this->stepTable([
                    'Event ID',
                    'Server',
                ], [[
                    $eventId,
                    $server->name,
                ]]);
            } catch (\Exception $e) {
                continue;
            }
        }
    }
}
