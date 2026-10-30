<?php

namespace App\Commands\Sites\Git;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasSiteIdParameter;
use App\Commands\Concerns\ParsesOptions;
use App\Commands\Concerns\ReadsDeployKeys;
use App\Questions\Ask;
use App\Questions\HasQuestions;
use App\Questions\Question;

class ConnectCommand extends BaseCommand
{
    use HasQuestions;
    use HasSiteIdParameter;
    use ParsesOptions;
    use ReadsDeployKeys;

    protected $signature = 'sites:git:connect
                            {site_id? : The site to connect a Git repository to}
                            {--repo= : The Git repository URL}
                            {--branch= : The Git branch to deploy from}
                            {--deploy-script= : The script that will run after each Git deployment}
                            {--push-to-deploy : Enable push to deploy}
                            {--private-key= : Path to a private key file to use as a unique deploy key for this site}
                            {--public-key= : Path to the public key file matching --private-key}
                            {--f|force : Run without prompting for confirmation}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'Connect a Git repository to a site';

    protected array $validationLabels = [
        'deploy_key.privatekey' => 'Private Key',
        'deploy_key.publickey'  => 'Public Key',
    ];

    protected function action(): int
    {
        $site = $this->selectSite('Which site would you like to connect a Git repository to', fn ($site) => !$site->git['enabled'], 'There are no sites with Git disabled.');

        if (is_null($site)) {
            return self::SUCCESS;
        }

        $answers = $this->askQuestions($this->nonInteractive());

        $payload = $this->withoutNulls([
            'repo'           => $answers['repo'],
            'branch'         => $answers['branch'],
            'deploy_script'  => $this->stringOption('deploy-script'),
            'push_to_deploy' => (bool) $this->option('push-to-deploy'),
            'deploy_key'     => $this->readDeployKey('private-key', 'public-key'),
        ]);

        $eventId = $site->connectGit($payload);

        $this->displaySiteEvent($site, $eventId, 'Git repository queued for connection.');

        return self::SUCCESS;
    }

    /**
     * @return Question[]
     */
    public function questions(): array
    {
        return [
            Ask::make('Repository')
                ->withFlag('repo'),

            Ask::make('Branch')
                ->withFlag('branch')
                ->withDefault('main'),
        ];
    }
}
