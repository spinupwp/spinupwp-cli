<?php

namespace App\Commands\Sites\SshKeys;

class RemoveCommand extends SshKeys
{
    protected $signature = 'sites:ssh-keys:remove
                            {site_id? : The site to remove the SSH key from}
                            {ssh_key? : The ID or name of the SSH key to remove}
                            {--f|force : Remove the SSH key without prompting for confirmation}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = "Remove one SSH key from a site's site user, keeping its other keys";

    protected function action(): int
    {
        $site = $this->selectSite('Which site would you like to remove an SSH key from');

        if (is_null($site)) {
            return self::SUCCESS;
        }

        $keys = $this->siteUserSshKeys($site);

        if ($keys->isEmpty()) {
            $this->warn('No SSH keys are authorized for the site user.');
            return self::SUCCESS;
        }

        $key = $this->selectSshKey($keys, 'Which SSH key would you like to remove', "No SSH key \"%s\" is authorized for the site user on \"{$site->domain}\".");

        if (!$this->forceOrConfirm("Are you sure you want to remove \"{$key['name']}\" from the site user on \"{$site->domain}\"?", false)) {
            return self::SUCCESS;
        }

        $eventId = $site->removeSiteUserSshKey($key['id']);

        $this->displaySiteEvent(
            $site,
            $eventId,
            "SSH key \"{$key['name']}\" queued for removal from the site user.",
            "SSH key \"{$key['name']}\" was not authorized for the site user."
        );

        return self::SUCCESS;
    }
}
