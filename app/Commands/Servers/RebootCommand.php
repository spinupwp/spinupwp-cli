<?php

namespace App\Commands\Servers;

use App\Commands\BaseCommand;

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
        if ((bool) $this->option('all')) {
            $this->actOnAllServers('reboot', 'rebootServers');
            return self::SUCCESS;
        }

        $serverId = $this->selectServer('reboot');

        $server = $this->spinupwp->getServer((int) $serverId);

        if ($this->forceOrConfirm("Are you sure you want to reboot \"{$server->name}\"?")) {
            $this->rebootServers([$server]);
        }

        return self::SUCCESS;
    }

    protected function actOnAllServers(string $action, string $callback): void
    {
        if ($this->forceOrConfirm(sprintf('Are you sure you want to %s all servers?', $action))) {
            $this->$callback($this->spinupwp->listServers()->toArray());
        }
    }

    protected function rebootServers(array $servers): void
    {
        if (empty($servers)) {
            return;
        }

        $events = [];

        foreach ($servers as $server) {
            try {
                $eventId  = $server->reboot();
                $events[] = ["{$eventId}", $server->name];
            } catch (\Exception $e) {
                if (count($servers) === 1) {
                    $this->error("{$server->name} could not be rebooted.");
                    return;
                }
            }
        }

        if (empty($events)) {
            $this->error('No servers could be rebooted.');
            return;
        }

        $this->successfulStep((count($events) === 1 ? 'Server' : 'Servers') . ' queued for reboot.');

        $this->stepTable([
            'Event ID',
            'Server',
        ], $events);
    }
}
