<?php

namespace App\Commands\Sites\SshKeys;

use Exception;
use SpinupWp\Resources\SshKey;

class AddCommand extends SshKeys
{
    protected $signature = 'sites:ssh-keys:add
                            {site_id? : The site to add the SSH key to}
                            {ssh_key? : The ID or name of the team SSH key to add}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = "Authorize one of your team's SSH keys for a site's site user, keeping its other keys";

    protected function action(): int
    {
        $site = $this->selectSite('Which site would you like to add an SSH key to');

        if (is_null($site)) {
            return self::SUCCESS;
        }

        if ($site->user_auth === 'password') {
            throw new Exception("The site user on \"{$site->domain}\" uses password authentication. Switch it to SSH keys with sites:site-user first.");
        }

        $authorizedIds = $this->siteUserSshKeys($site)->pluck('id');

        $keys = collect($this->spinupwp->listSshKeys())->map(fn (SshKey $key) => ['id' => $key->id, 'name' => $key->name]);

        // Only offer the keys that aren't authorized yet. A key named in the argument is always looked up.
        if (is_null($this->argument('ssh_key'))) {
            $keys = $keys->reject(fn ($key) => $authorizedIds->contains($key['id']))->values();

            if ($keys->isEmpty()) {
                $this->warn("All of your team's SSH keys are already authorized for the site user.");
                return self::SUCCESS;
            }
        }

        $key = $this->selectSshKey($keys, 'Which SSH key would you like to add', 'No SSH key "%s" found on your team.');

        $eventId = $site->addSiteUserSshKey($key['id']);

        $this->displaySiteEvent(
            $site,
            $eventId,
            "SSH key \"{$key['name']}\" queued for adding to the site user.",
            "SSH key \"{$key['name']}\" is already authorized for the site user."
        );

        return self::SUCCESS;
    }
}
