<?php

namespace App\Commands\SshKey;

use App\Commands\BaseCommand;
use App\Commands\Concerns\ReadsDeployKeys;
use App\Questions\Ask;
use Exception;

class CreateCommand extends BaseCommand
{
    use ReadsDeployKeys;

    protected $signature = 'ssh-key:create
                            {--name= : A name for the SSH key, such as the person or machine it belongs to}
                            {--public-key= : The path to the public key file, such as ~/.ssh/id_ed25519.pub}
                            {--f|force : Run without prompting}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'Add an SSH key to your team';

    protected function action(): int
    {
        foreach (['name', 'public-key'] as $option) {
            if ($this->nonInteractive() && !$this->option($option)) {
                throw new Exception("The --{$option} option is required when running with --force.");
            }
        }

        $name = Ask::make('Name')
            ->withFlag('name')
            ->nonInteractive($this->nonInteractive())
            ->resolveAnswer($this);

        $path = Ask::make('Path to the public key file')
            ->withFlag('public-key')
            ->withDefault('~/.ssh/id_ed25519.pub')
            ->nonInteractive($this->nonInteractive())
            ->resolveAnswer($this);

        $publicKey = $this->readKeyFile((string) $path);

        // Never send a private key to the API, even though it would reject it.
        if (str_contains($publicKey, 'PRIVATE KEY')) {
            throw new Exception("\"{$path}\" is a private key. Use the public key file, which usually ends in .pub.");
        }

        $sshKey = $this->spinupwp->sshKeys->create([
            'name'      => $name,
            'publickey' => $publicKey,
        ]);

        $this->successfulStep("SSH key \"{$sshKey->name}\" added to your team.");

        $this->stepTable(['ID', 'Name', 'Fingerprint'], [[$sshKey->id, $sshKey->name, $sshKey->fingerprint]]);

        return self::SUCCESS;
    }
}
