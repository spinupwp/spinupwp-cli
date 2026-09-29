<?php

namespace App\Commands\SshKey;

use App\Commands\SshKey\SshKeys;

class ListCommand extends SshKeys
{
    protected $signature = 'ssh-key:list
                            {--fields= : The fields to output}
                            {--format= : The output format (json or table)}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = "List your team's SSH keys";

    protected function action(): int
    {
        $sshKeys = $this->spinupwp->listSshKeys();

        if ($sshKeys->isEmpty()) {
            $this->warn('No SSH keys found.');
            return self::SUCCESS;
        }

        if ($this->shouldSpecifyFields()) {
            $this->saveFieldsFilter();
            $sshKeys->transform(fn ($sshKey) => $this->specifyFields($sshKey));
            $this->format($sshKeys);
            return self::SUCCESS;
        }

        if ($this->displayFormat() === 'table') {
            $sshKeys->transform(fn ($sshKey) => $this->specifyFields($sshKey, [
                'id',
                'name',
                'fingerprint',
                'created_at',
            ]));
        }

        $this->format($sshKeys);

        return self::SUCCESS;
    }
}
