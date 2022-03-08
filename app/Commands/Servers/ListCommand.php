<?php

namespace App\Commands\Servers;

use App\Commands\Servers\Servers;

class ListCommand extends Servers
{
    protected $signature = 'servers:list
                            {--format= : The output format (json or table)}
                            {--profile=}
                            {--fields=}';

    protected $description = 'List all servers';

    protected function action(): int
    {
        $servers = collect($this->spinupwp->listServers());

        if ($servers->isEmpty()) {
            $this->warn('No servers found.');
            return self::SUCCESS;
        }

        if ($this->shouldSpecifyFields()) {
            $this->saveFieldsFilter();
            $servers->transform(fn ($server) => $this->specifyFields($server));
            $this->format($servers);
            return self::SUCCESS;
        }

        if ($this->displayFormat() === 'table') {
            $servers->transform(fn ($server) => $this->specifyFields($server, [
                'id',
                'name',
                'ip_address',
                'ubuntu_version',
                'database.server',
            ]));
        }

        $this->format($servers);

        return self::SUCCESS;
    }
}
