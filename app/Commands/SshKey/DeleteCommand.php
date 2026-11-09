<?php

namespace App\Commands\SshKey;

use App\Commands\BaseCommand;
use Exception;
use Illuminate\Support\Collection;
use SpinupWp\Resources\SshKey;

class DeleteCommand extends BaseCommand
{
    protected $signature = 'ssh-key:delete
                            {ssh_key? : The ID or name of the SSH key to delete}
                            {--f|force : Delete the SSH key without prompting for confirmation}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'Delete an SSH key from your team and every site user and sudo user that has it';

    protected function action(): int
    {
        $sshKeys = collect($this->spinupwp->listSshKeys());

        if ($sshKeys->isEmpty()) {
            $this->warn('No SSH keys found.');
            return self::SUCCESS;
        }

        $sshKey = $this->selectSshKey($sshKeys);

        if (!$this->forceOrConfirm("Are you sure you want to delete \"{$sshKey->name}\"? It will also be removed from every site user and sudo user that has it.", false)) {
            return self::SUCCESS;
        }

        $eventIds = $sshKey->delete();

        if (empty($eventIds)) {
            $this->successfulStep("SSH key \"{$sshKey->name}\" deleted.");
            return self::SUCCESS;
        }

        $this->successfulStep("SSH key \"{$sshKey->name}\" deleted and queued for removal from the site users and sudo users that had it.");

        $this->stepTable(['Event ID'], array_map(fn (int $eventId) => [$eventId], $eventIds));

        return self::SUCCESS;
    }

    /**
     * Find the SSH key named by the ssh_key argument (an ID or name), or ask which one.
     *
     * @param  Collection<array-key, SshKey>  $sshKeys
     */
    protected function selectSshKey(Collection $sshKeys): SshKey
    {
        $argument = $this->argument('ssh_key');

        if (is_string($argument) && $argument !== '') {
            // An ID wins over a name, and names aren't unique, so refuse to guess between keys with the same name.
            $sshKey = $sshKeys->first(fn (SshKey $sshKey) => (string) $sshKey->id === $argument);

            if (is_null($sshKey)) {
                $named = $sshKeys->filter(fn (SshKey $sshKey) => strcasecmp($sshKey->name, $argument) === 0);

                if ($named->count() > 1) {
                    throw new Exception("More than one SSH key is named \"{$argument}\". Use its ID instead.");
                }

                $sshKey = $named->first();
            }

            if (is_null($sshKey)) {
                throw new Exception("No SSH key \"{$argument}\" found on your team.");
            }

            return $sshKey;
        }

        $selected = $this->askToSelect('Which SSH key would you like to delete', $sshKeys->keyBy('id')->map(fn (SshKey $sshKey) => $sshKey->name)->all());

        return $sshKeys->firstOrFail(fn (SshKey $sshKey) => $sshKey->id === $selected);
    }
}
