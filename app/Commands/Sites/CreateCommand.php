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

    protected array $userInput;

    protected function action(): int
    {
        if (!in_array($this->argument('installation_method'), OptionsHelper::INSTALLATION_METHODS, true)) {
            $this->error('Invalid site type.');
            $this->newLine(1);
            return self::INVALID;
        }

        $server = $this->selectServer('deploy to')->first();

        $this->userInput = $this->doPrompts([
            'domain' => [
                'type'    => 'ask',
                'prompt'  => 'Domain Name',
                'default' => !$this->nonInteractive(),
            ],
        ], $this->nonInteractive());
        $this->userInput += $this->doPrompts($this->getPrompts(), $this->nonInteractive());

        $site = $this->spinupwp->createSite($server->id, array_merge($this->arguments(), $this->options(), $this->userInput));

        $this->successfulStep("{$site->domain} is {$site->status} (event_id = {$site->eventId()})");

        return self::SUCCESS;
    }

    protected function getPrompts(): array
    {
        $prompts = [
            'https_enabled' => [
                'type'    => 'confirm',
                'prompt'  => 'Enable HTTPS',
                'default' => (int) !$this->nonInteractive(),
            ],
            'site_user' => [
                'type'    => 'ask',
                'prompt'  => 'Site user',
                'default' => $this->getDomainSlug(),
            ],
            'db_name' => [
                'type'    => 'ask',
                'prompt'  => 'Database name',
                'default' => $this->getDomainSlug(),
            ],
            'db_user' => [
                'type'    => 'ask',
                'prompt'  => 'Database username',
                'default' => $this->getDomainSlug(),
            ],
            'db_pass' => [
                'type'    => 'ask',
                'prompt'  => 'Database password',
                'default' => Str::random(12),

            ],
            'wp_title' => [
                'type'    => 'ask',
                'prompt'  => 'WordPress title',
                'default' => null,
            ],
            'wp_admin_email' => [
                'type'    => 'ask',
                'prompt'  => 'WordPress admin email address',
                'default' => null,
            ],
            'wp_admin_user' => [
                'type'    => 'ask',
                'prompt'  => 'WordPress admin username',
                'default' => null,
            ],
            'wp_admin_pass' => [
                'type'    => 'ask',
                'prompt'  => 'WordPress admin password',
                'default' => Str::random(12),
            ],
            'php_version' => [
                'type'    => 'choice',
                'prompt'  => 'PHP version',
                'default' => '8.0',
                'choices' => OptionsHelper::PHP_VERSIONS,
            ],
            'page_cache_enabled' => [
                'type'    => 'confirm',
                'prompt'  => 'Enable page cache',
                'default' => (int) !$this->nonInteractive(),
            ],
        ];

        switch ($this->argument('installation_method')) {
            case 'blank':
                return Arr::except($prompts, [
                    'db_name', 'db_user', 'db_pass',
                    'wp_title', 'wp_admin_user', 'wp_admin_email', 'wp_admin_pass',
                ]);
            default:
                return $prompts;
        }
    }

    public function getDomainSlug(): string
    {
        return str_replace('.', '', $this->userInput['domain']);
    }
}
