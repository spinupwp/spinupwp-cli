<?php

namespace App\Commands\Concerns;

trait InteractsWithRemote
{
    protected function ssh($user, $host, int $port = 22): int
    {
        passthru("ssh -t {$user}@{$host} -p {$port}", $exitCode);

        return (int) $exitCode;
    }
}
