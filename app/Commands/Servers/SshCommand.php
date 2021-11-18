<?php

namespace App\Commands\Servers;

use App\Commands\BaseCommand;
use App\Commands\Concerns\InteractsWithIO;
use App\Commands\Concerns\InteractsWithRemote;

class SshCommand extends BaseCommand
{
    use InteractsWithIO;
    use InteractsWithRemote;

    protected $signature = 'servers:ssh
                            {server_id?}
                            {user?}
                            {--profile=}';

    protected $description = 'Start an SSH session';

    protected function action(): int
    {
        $serverId = $this->argument('server_id');

        if (empty($serverId)) {
            $serverId = $this->askToSelectServer('Which server would you like to start an SSH session for');
        }

        $user = $this->argument('user');

        if (empty($user)) {
            $user = $this->ask('Which user would you like to connect as');
        }

        $server = $this->spinupwp->servers->get((int)$serverId);

        $this->line("Establishing a secure connection to [<comment>{$server->name}</comment>] as [<comment>{$user}</comment>]...");

        return $this->ssh(
            $user,
            $server->ip_address,
            $server->ssh_port,
        );
    }
}
