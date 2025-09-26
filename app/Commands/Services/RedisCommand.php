<?php

namespace App\Commands\Services;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasServerIdParameter;

class RedisCommand extends BaseCommand
{
    use HasServerIdParameter;

    protected $signature = 'services:redis
                            {server_id? : The server to restart PHP on}
                            {--all : Restart Redis on all servers}
                            {--f|force : Restart Redis without prompting for confirmation}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'Restart Redis';

    public function action(): int
    {
        if ($this->option('all') && $this->forceOrConfirm('Are you sure you want to restart Redis on all servers?')) {
            $servers = $this->spinupwp->listServers();
        } else {
            $servers = $this->selectServer('restart Redis on');
        }

        $this->queueResources($servers, 'restartRedis', 'Redis restart');

        return self::SUCCESS;
    }
}
