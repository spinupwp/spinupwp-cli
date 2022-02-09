<?php

namespace App\Commands\Servers;

use App\Commands\Servers\Servers;

class GetCommand extends Servers
{
    protected $signature = 'servers:get
                            {server_id : The server to output}
                            {--format=}
                            {--profile=}
                            {--fields=}';

    protected $description = 'Get a server';

    public function action(): int
    {
        $serverId = $this->argument('server_id');
        $server   = $this->spinupwp->getServer((int) $serverId);

        if ($this->shouldSpecifyFields()) {
            $this->saveFieldsFilter();
            $this->format($this->specifyFields($server));
            return self::SUCCESS;
        }

        if ($this->displayFormat() === 'table') {
            $server = $this->specifyFields($server, [
                'id',
                'name',
                'ip_address',
                'ubuntu_version',
                'database.server',
            ]);
        }

        $this->format($server);

        return self::SUCCESS;
    }
}
