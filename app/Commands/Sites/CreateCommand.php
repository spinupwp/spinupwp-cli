<?php

namespace App\Commands\Sites;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasServerIdParameter;
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

    protected $signature = 'sites:create
                            {server_id? : Server ID}
                            {--installation_method= : Type of installation (wp or blank)}
                            {--domain= : Domain name}
                            {--site_user= : name for unique system user who will have ownership permission of all the site files}
                            {--db_name= : name of a database to be created. Must be unique for the server}
                            {--db_user= : database level username to use when accessing the database}
                            {--db_pass= : database level password to use when accessing the database}
                            {--wp_title= : the title of your WordPress site}
                            {--wp_admin_user= : for a WordPress site, the admin user\'s username}
                            {--wp_admin_email= : for a WordPress site, the admin user\'s email}
                            {--wp_admin_pass= : for a Wordpress site, the admin user\'s password}
                            {--php_version= : PHP version the site will run under}
                            {--page_cache_enabled : enabling this option will configure Nginx FastCGI caching that is optimized for WordPress}
                            {--https_enabled : enabling secures your site by serving traffic over HTTPS}
                            {--profile=}
                            {--f|force}';

    protected $description = 'Create a site';

    protected array $userInput;

    protected array $validationLabels = [
        'domain'                   => 'Primary Domain',
        'page_cache.enabled'       => 'Enable Page Cache',
        'https.enabled'            => 'Enable HTTPS',
        'database.name'            => 'Database Name',
        'database.username'        => 'Database Username',
        'database.password'        => 'Database Password',
        'wordpress.title'          => 'WordPress Title',
        'wordpress.admin_user'     => 'WordPress Admin Username',
        'wordpress.admin_email'    => 'WordPress Admin Email',
        'wordpress.admin_password' => 'WordPress Admin Password',
    ];

    protected function action(): int
    {
        $server = $this->selectServer('deploy to')->first();

        if (is_null($server)) {
            return self::INVALID;
        }

        $this->userInput['installation_method'] = Choice::make('Installation Method')
            ->withChoices(OptionsHelper::INSTALLATION_METHODS)
            ->nonInteractive($this->nonInteractive())
            ->resolveAnswer($this);

        if (!in_array($this->userInput['installation_method'], OptionsHelper::INSTALLATION_METHODS, true)) {
            $this->error('Invalid site type.');
            $this->newLine(1);
            return self::INVALID;
        }

        $this->userInput['domain'] = Ask::make('Primary Domain')
            ->withFlag('domain')
            ->nonInteractive($this->nonInteractive())
            ->resolveAnswer($this);

        $this->userInput += $this->askQuestions($this->nonInteractive());

        $this->userInput = array_merge($this->arguments(), $this->options(), $this->userInput);

        $site = $this->spinupwp->createSite($server->id, $this->userInput);

        $this->displaySuccess($site->eventId());

        return self::SUCCESS;
    }

    public function getDomainSlug(): string
    {
        return str_replace('.', '', $this->userInput['domain']);
    }

    public function questions(): array
    {
        $commonStart = [
            Confirm::make('Enable HTTPS')
                ->withFlag('https_enabled')
                ->withDefault((bool) !$this->nonInteractive()),

            Ask::make('Site User')
                ->withDefault($this->getDomainSlug()),
        ];

        $db = [
            Ask::make('Database Name')
                ->withFlag('db_name')
            ->withDefault($this->getDomainSlug()),

            Ask::make('Database Username')
                ->withFlag('db_user')
                ->withDefault($this->getDomainSlug()),

            Ask::make('Database Password')
                ->withFlag('db_pass')
                ->withDefault(Str::random(12)),
        ];

        $wp = [
            Ask::make('WordPress Title')
                ->withFlag('wp_title'),

            Ask::make('WordPress Admin Email')
                ->withFlag('wp_admin_email'),

            Ask::make('WordPress Admin Username')
                ->withFlag('wp_admin_user'),

            Ask::make('WordPress Admin Password')
                ->withFlag('wp_admin_pass')
                ->withDefault(Str::random(12)),
        ];

        $commonEnd = [
            Choice::make('PHP Version')
                ->withFlag('php_version')
                ->withChoices(OptionsHelper::PHP_VERSIONS)
                ->withDefault('8.0'),

            Confirm::make('Enable Page Cache')
                ->withFlag('page_cache_enabled')
                ->withDefault((bool) !$this->nonInteractive()),
        ];

        switch ($this->userInput['installation_method']) {
            case 'blank':
                return array_merge($commonStart, $commonEnd);
            default:
                return array_merge(
                    $commonStart,
                    $db,
                    $wp,
                    $commonEnd
                );
        }
    }

    protected function displaySuccess($eventId): void
    {
        $tableHeadings = [
            'Event ID',
            'Site',
        ];

        $tableRow = [
            $eventId,
            $this->userInput['domain'],
        ];

        if ($this->userInput['installation_method'] === 'wp') {
            $tableHeadings = array_merge($tableHeadings, [
                'Database Password',
                'WordPress Admin Password',
            ]);

            $tableRow = array_merge($tableRow, [
                $this->userInput['db_pass'],
                $this->userInput['wp_admin_pass'],
            ]);
        }

        $this->successfulStep('Site queued for creation.');

        $this->stepTable($tableHeadings, [$tableRow]);
    }
}
