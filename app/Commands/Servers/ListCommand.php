<?php

namespace App\Commands\Servers;

use App\Commands\BaseCommand;

class ListCommand extends BaseCommand
{
    protected $signature = 'servers:list {--format=}';

    protected $description = 'Retrieve a list or servers';

    protected function action()
    {
        $servers = collect($this->spinupwp->servers->list()->toArray());
        $servers->transform(function ($server) {
            $server = $server->toArray();
            unset($server['ssh_publickey']);
            unset($server['git_publickey']);
            return $server;
        });

        return $servers;
    }
}
