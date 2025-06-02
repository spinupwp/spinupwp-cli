<?php

namespace App\Commands\Servers;

use App\Commands\BaseCommand;
use App\Questions\Ask;
use App\Questions\Choice;
use App\Questions\HasQuestions;
use App\Questions\Question;

class CreateCustomCommand extends BaseCommand
{
    use HasQuestions;

    public const UBUNTU_VERSIONS = ['24.04', '22.04'];

    public const AUTH_METHODS = ['publickey', 'password'];

    protected $signature = 'servers:create-custom
                            {--provider-name= : Server provider name (e.g. OVH)}
                            {--ubuntu-version= : Ubuntu version (must be one of the two latest LTS versions, e.g. 24.04)}
                            {--ip-address= : Public IP address of the server}
                            {--ssh-port= : SSH port (default: 22)}
                            {--ssh-username= : SSH username}
                            {--ssh-auth-method= : SSH authentication method (password or publickey)}
                            {--ssh-password= : SSH password (if auth-method is password)}
                            {--hostname= : Server hostname}
                            {--timezone= : Server timezone (e.g. UTC, America/Toronto)}
                            {--post-provision-script= : Path to a script to run as root after provisioning}
                            {--database-root-password= : Root password for the database}
                            {--database-provider-id= : ID of external database from SpinupWP settings}
                            {--f|force : Run without prompting for confirmation}
                            {--profile= : SpinupWP configuration profile to use}';

    protected $description = 'Provision a custom server';

    /**
     * @var array<string, string|null>
     */
    protected array $userInput = [];

    protected function action(): int
    {
        $this->userInput = $this->askQuestions($this->nonInteractive());

        $server = $this->spinupwp->createCustomServer($this->userInput);

        $this->displaySuccess(intval($server->eventId()));

        return self::SUCCESS;
    }

    /**
     * @return Question[]
     */
    public function questions(): array
    {
        return [
            Ask::make('Provider Name')
                ->withFlag('provider-name'),

            Choice::make('Ubuntu Version')
                ->withFlag('ubuntu-version')
                ->withChoices(self::UBUNTU_VERSIONS)
                ->withDefault(self::UBUNTU_VERSIONS[0]),

            Ask::make('IP Address')
                ->withFlag('ip-address'),

            Ask::make('SSH Port')
                ->withFlag('ssh-port')
                ->withDefault('22'),

            Ask::make('SSH Username')
                ->withFlag('ssh-username'),

            Choice::make('SSH Authentication Method')
                ->withFlag('ssh-auth-method')
                ->withChoices(self::AUTH_METHODS)
                ->withDefault(self::AUTH_METHODS[0]),

            Ask::make('SSH Password')
                ->withFlag('ssh-password')
                ->unless(fn (array $answers): bool => $answers['ssh-auth-method'] === 'publickey'),

            Ask::make('Hostname')
                ->withFlag('hostname'),

            Ask::make('Timezone')
                ->withFlag('timezone')
                ->withDefault('UTC'),

            Ask::make('Post-Provision Script')
                ->withFlag('post-provision-script'),

            Ask::make('Database Root Password')
                ->withFlag('database-root-password'),

            Ask::make('Database Provider ID')
                ->withFlag('database-provider-id'),
        ];
    }

    protected function displaySuccess(int $eventId): void
    {
        $tableHeadings = [
            'Event ID',
            'Provider name',
        ];

        $tableRow = [
            $eventId,
            $this->userInput['provider-name'],
        ];

        $this->successfulStep('Server queued for creation.');

        $this->stepTable($tableHeadings, [$tableRow]);
    }
}
