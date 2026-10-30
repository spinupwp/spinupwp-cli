<?php

namespace App\Commands\Sites;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasSiteIdParameter;
use App\Commands\Concerns\ParsesOptions;
use App\Questions\Ask;
use App\Questions\Choice;
use App\Questions\HasQuestions;
use App\Questions\Question;
use Exception;
use Symfony\Component\Console\Question\ChoiceQuestion;

class SiteUserCommand extends BaseCommand
{
    use HasQuestions;
    use HasSiteIdParameter;
    use ParsesOptions;

    protected $signature = 'sites:site-user
                            {site_id? : The site to update}
                            {--authentication= : The SSH authentication method (publickey or password)}
                            {--ssh-key-id=* : The ID of an SSH key to authorize for the site user (repeatable)}
                            {--password= : The site user\'s password}
                            {--f|force : Run without prompting for confirmation}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = "Update the site user's SSH authentication";

    protected array $validationLabels = [
        'ssh_key_ids' => 'SSH Keys',
    ];

    protected function action(): int
    {
        $site = $this->selectSite('Which site would you like to update the site user for');

        if (is_null($site)) {
            return self::SUCCESS;
        }

        $answers = $this->askQuestions($this->nonInteractive());

        $payload = ['authentication' => $answers['authentication']];

        if ($answers['authentication'] === 'publickey') {
            $payload['ssh_key_ids'] = $this->sshKeyIds();
        }

        if ($answers['authentication'] === 'password') {
            $payload['password'] = $answers['password'];
        }

        $eventId = $site->updateSiteUser($payload);

        $this->displaySiteEvent($site, $eventId, 'Site user queued for update.', 'Site user updated.');

        return self::SUCCESS;
    }

    /**
     * @return Question[]
     */
    public function questions(): array
    {
        return [
            Choice::make('How should the site user authenticate over SSH?')
                ->withFlag('authentication')
                ->withChoices([
                    'publickey' => 'SSH keys',
                    'password'  => 'Password',
                ])
                ->withDefault('publickey'),

            Ask::make('Password')
                ->withFlag('password')
                ->hidden()
                ->unless(fn (array $answers): bool => $answers['authentication'] !== 'password'),
        ];
    }

    protected function sshKeyIds(): array
    {
        $ids = $this->option('ssh-key-id');

        if (is_array($ids) && !empty($ids)) {
            return array_map(function ($id) {
                if (!ctype_digit((string) $id)) {
                    throw new Exception('The --ssh-key-id option must be an integer.');
                }

                return (int) $id;
            }, $ids);
        }

        if ($this->nonInteractive()) {
            throw new Exception('The --ssh-key-id option is required when running with --force.');
        }

        $sshKeys = $this->spinupwp->listSshKeys();

        if ($sshKeys->isEmpty()) {
            throw new Exception('No SSH keys found. Add an SSH key to your team in SpinupWP first.');
        }

        $question = new class('Which SSH keys should be authorized for the site user? (comma-separated)', $sshKeys->keyBy('id')->map(fn ($sshKey) => $sshKey->name)->toArray()) extends ChoiceQuestion {
            public function isAssoc(array $array): bool
            {
                return true;
            }
        };

        $question->setMultiselect(true);

        return array_map('intval', (array) $this->output->askQuestion($question));
    }
}
