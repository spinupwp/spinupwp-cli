<?php

namespace App\Commands\Servers;

use App\Commands\BaseCommand;

class ListCommand extends BaseCommand
{
    protected $signature = 'servers:list
                            {--format=}
                            {--profile=}';

    protected $description = 'List all servers';

    protected function action(): int
    {
        $servers = collect($this->spinupwp->listServers());

        if ($servers->isEmpty()) {
            $this->warn('No servers found.');
            return self::SUCCESS;
        }

        if ($this->displayFormat() === 'table') {
            $servers->transform(fn ($server) => [
                'ID'         => $server->id,
                'Name'       => $server->name,
                'IP Address' => $server->ip_address,
                'Ubuntu'     => $server->ubuntu_version,
                'Database'   => $server->database['server'],
            ]);
        }

        $this->format($servers);

        return self::SUCCESS;
    }
}
