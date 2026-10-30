<?php

namespace App\Commands\Sites;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasServerIdParameter;
use App\Commands\Concerns\ReadsDeployKeys;
use App\Helpers\OptionsHelper;
use App\Questions\Ask;
use App\Questions\Choice;
use App\Questions\Confirm;
use App\Questions\HasQuestions;
use Illuminate\Support\Str;

class CreateCommand extends BaseCommand
{
    use HasQuestions;
    use HasServerIdParameter;
    use ReadsDeployKeys;

    protected $signature = 'sites:create
                            {server_id? : Server ID}
                            {--installation-method= : Type of installation (wp, wp_subdirectory, wp_subdomain, git or blank)}
                            {--domain= : Domain name}
                            {--site-user= : Name for unique system user who will have ownership permission of all the site files}
                            {--public-folder= : The folder to serve the site from, relative to the site\'s files directory (defaults to /)}
                            {--db-name= : Name of a database to be created. Must be unique for the server}
                            {--db-user= : Database username to use when accessing the database}
                            {--db-pass= : Database password to use when accessing the database}
                            {--db-table-prefix= : For a WordPress site, the database table prefix (randomly generated if not set)}
                            {--wp-title= : The title of your WordPress site}
                            {--wp-admin-user= : For a WordPress site, the admin user\'s username}
                            {--wp-admin-email= : For a WordPress site, the admin user\'s email}
                            {--wp-admin-pass= : For a Wordpress site, the admin user\'s password}
                            {--git-repo= : For a Git site, the repository to deploy, in SSH format (e.g. git@github.com:org/repo.git)}
                            {--git-branch= : For a Git site, the branch to deploy from}
                            {--push-to-deploy : For a Git site, enable push to deploy}
                            {--always-run-deploy-script : For a Git site, run the deploy script after every deployment}
                            {--git-private-key= : For a Git site, path to a private key file to use as a unique deploy key}
                            {--git-public-key= : For a Git site, path to the public key file matching --git-private-key}
                            {--deploy-script= : A script to run after the site has been deployed}
                            {--php-version= : PHP version the site will run under}
                            {--page-cache-enabled : Enabling this option will configure page caching that is optimized for WordPress}
                            {--f|force : Create the site without prompting for confirmation}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'Create a site';

    protected array $userInput;

    protected array $validationLabels = [
        'domain'                   => 'Primary Domain',
        'page_cache.enabled'       => 'Enable Page Cache',
        'public_folder'            => 'Public Folder',
        'database.name'            => 'Database Name',
        'database.username'        => 'Database Username',
        'database.password'        => 'Database Password',
        'database.table_prefix'    => 'Database Table Prefix',
        'wordpress.title'          => 'WordPress Title',
        'wordpress.admin_user'     => 'WordPress Admin Username',
        'wordpress.admin_email'    => 'WordPress Admin Email',
        'wordpress.admin_password' => 'WordPress Admin Password',
        'git.repo'                 => 'Git Repository',
        'git.branch'               => 'Git Branch',
        'git.deploy_key.publickey' => 'Git Public Key',
    ];

    protected function action(): int
    {
        $server = $this->selectServer('deploy to')->first();

        if (is_null($server)) {
            return self::INVALID;
        }

        $this->userInput['installation-method'] = Choice::make('What files would you like SpinupWP to install?')
            ->withFlag('installation-method')
            ->withChoices(OptionsHelper::INSTALLATION_METHODS)
            ->withDefault(array_key_first(OptionsHelper::INSTALLATION_METHODS))
            ->nonInteractive($this->nonInteractive())
            ->resolveAnswer($this);

        if (!array_key_exists($this->userInput['installation-method'], OptionsHelper::INSTALLATION_METHODS)) {
            $this->error('Invalid installation method.');
            $this->newLine(1);
            return self::INVALID;
        }

        $this->userInput['domain'] = Ask::make('Primary Domain')
            ->withFlag('domain')
            ->nonInteractive($this->nonInteractive())
            ->resolveAnswer($this);

        $this->userInput += $this->askQuestions($this->nonInteractive());

        $this->userInput = array_merge($this->arguments(), $this->options(), $this->userInput);

        if ($this->isGitSite()) {
            $this->userInput['git-deploy-key'] = $this->readDeployKey('git-private-key', 'git-public-key');
        }

        if ($this->isWordPressSite()) {
            $saveDefaults = [
                'save-admin-email'    => data_get($this->userInput, 'save-admin-email', false),
                'save-admin-username' => data_get($this->userInput, 'save-admin-username', false),
            ];

            unset($this->userInput['save-admin-email']);
            unset($this->userInput['save-admin-username']);

            $this->saveDefaults($saveDefaults);
        }

        $site = $this->spinupwp->createSite($server->id, $this->userInput);

        $this->displaySuccess(intval($site->eventId()), $site->id);

        return self::SUCCESS;
    }

    protected function isWordPressSite(): bool
    {
        return in_array($this->userInput['installation-method'], OptionsHelper::WORDPRESS_INSTALLATION_METHODS, true);
    }

    protected function isGitSite(): bool
    {
        return $this->userInput['installation-method'] === 'git';
    }

    public function getDomainSlug(): string
    {
        return str_replace('.', '', (string) ($this->userInput['domain'] ?? ''));
    }

    public function questions(): array
    {
        $commonStart = [
            Ask::make('Site User')
                ->withDefault($this->getDomainSlug()),
        ];

        $db = [
            Ask::make('Database Name')
                ->withFlag('db-name')
                ->withDefault($this->getDomainSlug()),

            Ask::make('Database Username')
                ->withFlag('db-user')
                ->withDefault($this->getDomainSlug()),

            Ask::make('Database Password')
                ->withFlag('db-pass')
                ->withDefault(Str::random(12)),
        ];

        $wp = [
            Ask::make('WordPress Title')
                ->withFlag('wp-title'),

            Ask::make('WordPress Admin Email')
                ->withFlag('wp-admin-email')
                ->withDefault($this->getDefaultsFromConfiguration('wp-admin-email')),

            Confirm::make('Do you want to save this Admin Email as the default for WordPress sites?')
                ->withFlag('')
                ->withKey('save-admin-email')
                ->withDefault(false)
                ->unless(fn () => $this->getDefaultsFromConfiguration('wp-admin-email')),

            Ask::make('WordPress Admin Username')
                ->withFlag('wp-admin-user')
                ->withDefault($this->getDefaultsFromConfiguration('wp-admin-user')),

            Confirm::make('Do you want to save this Admin Username as the default for WordPress sites?')
                ->withFlag('')
                ->withKey('save-admin-username')
                ->withDefault(false)
                ->unless(fn () => $this->getDefaultsFromConfiguration('wp-admin-user')),

            Ask::make('WordPress Admin Password')
                ->withFlag('wp-admin-pass')
                ->withDefault(Str::random(12)),
        ];

        $git = [
            Ask::make('Git Repository')
                ->withFlag('git-repo'),

            Ask::make('Git Branch')
                ->withFlag('git-branch')
                ->withDefault('main'),

            Ask::make('Public Folder')
                ->withFlag('public-folder')
                ->withDefault('/'),

            Confirm::make('Enable Push to Deploy')
                ->withFlag('push-to-deploy')
                ->withDefault(false),
        ];

        $commonEnd = [
            Choice::make('PHP Version')
                ->withFlag('php-version')
                ->withChoices(OptionsHelper::PHP_VERSIONS)
                ->withDefault(OptionsHelper::DEFAULT_PHP_VERSION),

            Confirm::make('Enable Page Cache')
                ->withFlag('page-cache-enabled')
                ->withDefault((bool) !$this->nonInteractive()),
        ];

        if ($this->isWordPressSite()) {
            return array_merge($commonStart, $db, $wp, $commonEnd);
        }

        if ($this->isGitSite()) {
            return array_merge($commonStart, $git, $commonEnd);
        }

        return array_merge($commonStart, $commonEnd);
    }

    /** @return mixed */
    protected function getDefaultsFromConfiguration(string $question)
    {
        return data_get($this->config->getCommandConfiguration('sites:create', $this->profile()), $question);
    }

    protected function saveDefaults(array $options): void
    {
        if ($options['save-admin-email']) {
            $this->config->setCommandConfiguration('sites:create', 'wp-admin-email', $this->userInput['wp-admin-email'], $this->profile());
        }

        if ($options['save-admin-username']) {
            $this->config->setCommandConfiguration('sites:create', 'wp-admin-user', $this->userInput['wp-admin-user'], $this->profile());
        }
    }

    protected function displaySuccess(int $eventId, int $siteId): void
    {
        $tableHeadings = [
            'Event ID',
            'Site ID',
            'Site',
        ];

        $tableRow = [
            $eventId,
            $siteId,
            $this->userInput['domain'],
        ];

        if ($this->isWordPressSite()) {
            $tableHeadings = array_merge($tableHeadings, [
                'Database Password',
                'WordPress Admin Password',
            ]);

            $tableRow = array_merge($tableRow, [
                $this->userInput['db-pass'],
                $this->userInput['wp-admin-pass'],
            ]);
        }

        $this->successfulStep('Site queued for creation.');

        $this->stepTable($tableHeadings, [$tableRow]);
    }
}
