<?php

namespace App\Commands\Sites\SshKeys;

class ListCommand extends SshKeys
{
    protected $signature = 'sites:ssh-keys:list
                            {site_id? : The site to list the site user\'s SSH keys for}
                            {--format= : The output format (json or table)}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = "List the SSH keys authorized for a site's site user";

    protected function action(): int
    {
        $site = $this->selectSite('Which site would you like to list the site user\'s SSH keys for');

        if (is_null($site)) {
            return self::SUCCESS;
        }

        $keys = $this->siteUserSshKeys($site);

        if ($keys->isEmpty()) {
            $this->warn('No SSH keys are authorized for the site user.');
            return self::SUCCESS;
        }

        if ($this->displayFormat() === 'table') {
            $keys = $keys->map(fn ($key) => [
                'ID'          => $key['id'],
                'Name'        => $key['name'],
                'Fingerprint' => $key['fingerprint'],
            ]);
        }

        $this->format($keys);

        return self::SUCCESS;
    }
}
