<?php

namespace App\Commands\Servers;

use App\Commands\BaseCommand;
use App\Commands\Concerns\InteractsWithIO;

class ListCommand extends BaseCommand
{
    use InteractsWithIO;

    protected $signature = 'servers:list {--format=} {--profile=}';

    protected $description = 'Retrieves a list of servers';

    protected function action(): int
    {
        $servers = collect($this->spinupwp->servers->list());

        if ($this->displayFormat() === 'table') {
            $servers->transform(fn($server) => [
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
