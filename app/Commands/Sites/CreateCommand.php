<?php

namespace App\Commands\Sites;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasPrompts;
use App\Commands\Concerns\InteractsWithIO;
use App\Commands\Concerns\SelectsServer;
use App\Helpers\OptionsHelper;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CreateCommand extends BaseCommand
{
    use InteractsWithIO;
    use HasPrompts;
    use SelectsServer;

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

    protected ?string $domain = '';

    protected function action(): int
    {
        if (!in_array($this->argument('installation_method'), OptionsHelper::INSTALLATION_METHODS, true)) {
            $this->error('Invalid site type.');
            $this->newLine(1);
            return self::INVALID;
        }

        $server       = $this->selectServer('deploy to')->first();
        $this->domain = $this->option('domain') ?? $this->resolveAnswer(['prompt' => 'Domain Name'], $this->nonInteractive());
        $userInput    = $this->promptForAnswers($this->nonInteractive());

        $site = $this->spinupwp->createSite($server->id, array_merge($this->arguments(), $this->options(), ['domain' => $this->domain], $userInput));

        $this->successfulStep("{$site->domain} is {$site->status} (event_id = {$site->eventId()})");

        return self::SUCCESS;
    }

    protected function getPrompts(): array
    {
        $prompts = [
            'https_enabled' => [
                'default'               => 1,
                'nonInteractiveDefault' => 0,
                'type'                  => 'confirm',
                'prompt'                => 'Enable HTTPS',
            ],
            'site_user' => [
                'prompt'          => 'Site User',
                'defaultCallback' => [$this, 'getDomainSlug'],
            ],
            'db_name' => [
                'prompt'          => 'Database name',
                'defaultCallback' => [$this, 'getDomainSlug'],
            ],
            'db_pass' => [
                'prompt'          => 'Database Password',
                'defaultCallback' => fn () => Str::random(12),
            ],
            'wp_title'       => ['prompt' => 'WordPress Title'],
            'wp_admin_email' => ['prompt' => 'WordPress admin email address'],
            'wp_admin_user'  => ['prompt' => 'WordPress admin username'],
            'wp_admin_pass'  => [
                'prompt'          => 'WordPress admin password',
                'defaultCallback' => fn () => Str::random(12),
            ],
            'php_version' => [
                'type'    => 'choice',
                'prompt'  => 'PHP Version',
                'default' => '8.0',
                'choices' => OptionsHelper::PHP_VERSIONS,
            ],
            'page_cache_enabled' => [
                'default'               => 1,
                'nonInteractiveDefault' => 0,
                'type'                  => 'confirm',
                'prompt'                => 'Enable page cache',
            ],
        ];

        switch ($this->argument('installation_method')) {
            case 'blank':
                return Arr::except($prompts, [
                    'db_name', 'db_user', 'db_pass',
                    'wp_title', 'wp_admin_user', 'wp_admin_email', 'wp_admin_pass',
                ]);
            case 'git':
                return Arr::except($prompts, [
                    'wp_title', 'wp_admin_user', 'wp_admin_email', 'wp_admin_pass',
                ]);
            default:
                return $prompts;
        }
    }

    public function getDomainSlug(): string
    {
        return str_replace('.', '', $this->domain);
    }
}
