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
                            {installation_method : Type of installation (wp or blank)}
                            {server_id? : Server ID}
                            {--domain= : Domain name}
                            {--site_user=}
                            {--db_name=}
                            {--db_user=}
                            {--db_pass=}
                            {--wp_title=}
                            {--wp_admin_user=}
                            {--wp_admin_email=}
                            {--wp_admin_pass=}
                            {--php_version=}
                            {--page_cache_enabled}
                            {--https_enabled}
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
        if (!in_array($this->argument('installation_method'), OptionsHelper::INSTALLATION_METHODS, true)) {
            $this->error('Invalid site type.');
            $this->newLine(1);
            return self::INVALID;
        }

        $server = $this->selectServer('deploy to')->first();

        if (is_null($server)) {
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

        switch ($this->argument('installation_method')) {
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
