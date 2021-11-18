<?php

namespace App\Commands\Concerns;

trait InteractsWithRemote
{
    protected function ssh(string $user, string $host, int $port = 22, string $command = ''): int
    {
        $options = collect([
            'ConnectTimeout' => 5,
            'ControlMaster'  => 'auto',
            'ControlPath'    => $this->config->sshControlPath(),
            'ControlPersist' => 100,
            'LogLevel'       => 'QUIET',
        ])->map(function ($value, $option) {
            return "-o $option=$value";
        })->implode(' ');

        passthru("ssh {$options} -t {$user}@{$host} -p {$port} '{$command}'", $exitCode);

        return (int)$exitCode;
    }
}
