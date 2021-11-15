<?php

namespace App\Commands\Servers;

use App\Commands\BaseCommand;

class ListCommand extends BaseCommand
{
    protected $signature = 'servers:list {--format=} {--profile=}';

    protected $description = 'Retrieves a list of servers';

    protected function action()
    {
        $servers = collect($this->spinupwp->servers->list());

        if ($this->displayFormat() === 'json') {
            return $servers;
        }

        return $servers->map(fn ($item) => [
            'ID'         => $item->id,
            'Name'       => $item->name,
            'IP Address' => $item->ip_address,
        ]);
    }
}
