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
                            {server_id? : The server to connect to}
                            {user? : The SSH user to connect as}
                            {--profile=}';

    protected $description = 'Start an SSH session';

    protected function action(): int
    {
        $serverId = $this->argument('server_id');

        if (empty($serverId)) {
            $serverId = $this->askToSelectServer('Which server would you like to start an SSH session for');
        }

        $server = $this->spinupwp->servers->get((int)$serverId);
        $user   = $this->establishUser();

        $this->line("Establishing a secure connection to [<comment>{$server->name}</comment>] as [<comment>{$user}</comment>]...");

        $exitCode = $this->ssh(
            $user,
            $server->ip_address,
            $server->ssh_port,
        );

        if ($exitCode === 255) {
            $this->error("Unable to connect to \"{$server->name}\". Have you added your SSH key to the \"{$user}\" user?");
        }

        return $exitCode;
    }

    protected function establishUser(): string
    {
        $user        = $this->argument('user');
        $defaultUser = $this->config->get('default_ssh_user', $this->profile(), null);

        if (is_string($user)) {
            if (is_null($defaultUser)) {
                $this->askToSetDefaultUser($user);
            }

            return $user;
        }

        if (!empty($defaultUser)) {
            return $defaultUser;
        }

        $user = $this->ask('Which user would you like to connect as');

        if (is_null($defaultUser)) {
            $this->askToSetDefaultUser($user);
        }

        return $user;
    }

    protected function askToSetDefaultUser(string $user): void
    {
        do {
            $response = strtolower($this->ask("Do you want to set \"{$user}\" as your default SSH user? (y/n)", 'y'));
        } while (!in_array($response, ['y', 'n']));

        $value = $response === 'y' ? $user : '';
        $this->config->set('default_ssh_user', $value, $this->profile());
    }
}
