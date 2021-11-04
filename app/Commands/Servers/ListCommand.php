<?php

namespace App\Commands\Servers;

use App\Commands\BaseCommand;

class ListCommand extends BaseCommand
{
    protected $signature = 'servers:list {--format=} {--profile=}';

    protected $description = 'Retrieve a list or servers';

    protected function action()
    {
        $servers = collect($this->spinupwp->servers->list());

        return $servers->map(fn ($item) => [
            'id'            => $item->id,
            'provider_name' => $item->provider_name,
            'name'          => $item->name,
            'ip_address'    => $item->ip_address,
        ]);
    }
}
