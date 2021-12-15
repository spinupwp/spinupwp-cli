<?php

namespace App\Commands\Servers;

use App\Commands\BaseCommand;

class RebootCommand extends BaseCommand
{
    protected $signature = 'servers:reboot
                            {server_id? : The server to reboot}
                            {--all : Reboot all servers}
                            {--force : Force reboot}
                            {--profile=}';

    protected $description = 'Reboot a server';

    public function action(): int
    {
        $serverId = $this->argument('server_id');

        if ((bool) $this->option('all')) {
            $this->rebootAllServers();
            return self::SUCCESS;
        }

        if (empty($serverId)) {
            $serverId = $this->askToSelectServer('Which server would you like to reboot?');
        }

        $server = $this->spinupwp->servers->get($serverId);

        $reboot = (bool) $this->option('force') || $this->confirm("Are you sure you want to reboot \"{$server->name}\"?", 'yes');

        if (!$reboot) {
            return self::SUCCESS;
        }

        $this->rebootServers([$server]);

        return self::SUCCESS;
    }

    protected function rebootAllServers(): void
    {
        $reboot = (bool) $this->option('force') || $this->confirm('Are you sure you want to reboot all servers?', 'yes');
        if ($reboot) {
            $this->rebootServers($this->spinupwp->servers->list());
        }
    }

    protected function rebootServers($servers): void
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
