<?php

namespace App\Commands\SshKey;

use App\Commands\BaseCommand;

class GetCommand extends BaseCommand
{
    protected $signature = 'ssh-key:get
                            {--format= : The output format (json or table)}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = "Get SpinupWP's SSH Key";

    public function action(): int
    {
        $this->largeOutput = true;
        $key               = $this->spinupwp->getSshKey();

        $this->format(['key' => $key]);

        return self::SUCCESS;
    }
}
