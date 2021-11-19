<?php

namespace App\Commands\Servers;

use App\Commands\BaseCommand;

class GetCommand extends BaseCommand
{
    protected $signature = 'servers:get {server_id} {--format=} {--profile=}';

    protected $description = 'Get a server';

    protected bool $largeOutput = true;

    public function action()
    {
        $serverId = $this->argument('server_id');

        $server = $this->spinupwp->servers->get($serverId);

        if ($this->displayFormat() === 'json') {
            return $server;
        }

        return [
            'ID'            => $server->id,
            'Name'          => $server->name,
            'Provider name' => $server->provider_name,
            'IP Address'    => $server->ip_address,
            'Ubuntu'        => $server->ubuntu_version,
            'Database'      => $server->database['server'],
            'SSH port'      => $server->ssh_port,
        ];
    }
}
