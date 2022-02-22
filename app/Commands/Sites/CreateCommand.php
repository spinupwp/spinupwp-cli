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

        $this->userInput['domain'] = Ask::make('Domain')
            ->nonInteractive($this->nonInteractive())
            ->resolveAnswer($this);

        $this->userInput += $this->askQuestions($this->nonInteractive());

        $site = $this->spinupwp->createSite($server->id, array_merge($this->arguments(), $this->options(), $this->userInput));

        $this->successfulStep("{$site->domain} is {$site->status} (event_id = {$site->eventId()})");

        return self::SUCCESS;
    }

    public function getDomainSlug(): string
    {
        return str_replace('.', '', $this->userInput['domain']);
    }

    public function questions(): array
    {
        $commonStart = [
            Confirm::make('Https Enabled')
                ->withDefault((bool) !$this->nonInteractive()),

            Ask::make('Site User')
                ->withDefault($this->getDomainSlug()),
        ];

        $db = [
            Ask::make('Db Name')
            ->withDefault($this->getDomainSlug()),

            Ask::make('Db User')
                ->withDefault($this->getDomainSlug()),

            Ask::make('Db Pass')
                ->withDefault(Str::random(12)),
        ];

        $wp = [
            Ask::make('WordPress Title')
                ->withFlag('wp_title'),

            Ask::make('WordPress admin email address')
                ->withFlag('wp_admin_email'),

            Ask::make('WordPress admin username')
                ->withFlag('wp_admin_user'),

            Ask::make('WordPress admin password')
                ->withFlag('wp_admin_pass')
                ->withDefault(Str::random(12)),
        ];

        $commonEnd = [
            Choice::make('PHP Version')
                ->withFlag('php_version')
                ->withChoices(OptionsHelper::PHP_VERSIONS)
                ->withDefault('8.0'),

            Confirm::make('Page Cache Enabled')
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
}
