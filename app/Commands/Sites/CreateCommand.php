<?php

namespace App\Commands\Sites;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasOptionsIO;
use App\Commands\Concerns\InteractsWithIO;
use App\Commands\Concerns\SelectsServer;
use App\Helpers\OptionsHelper;
use App\OptionsIO\Sites\DbName;
use App\OptionsIO\Sites\DbPass;
use App\OptionsIO\Sites\DbUser;
use App\OptionsIO\Sites\Domain;
use App\OptionsIO\Sites\HttpsEnabled;
use App\OptionsIO\Sites\PageCacheEnabled;
use App\OptionsIO\Sites\PhpVersion;
use App\OptionsIO\Sites\SiteUser;
use App\OptionsIO\Sites\WpAdminEmail;
use App\OptionsIO\Sites\WpAdminPass;
use App\OptionsIO\Sites\WpAdminUser;
use App\OptionsIO\Sites\WpTitle;
use Illuminate\Support\Arr;

class CreateCommand extends BaseCommand
{
    use InteractsWithIO;
    use HasOptionsIO;
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

    protected array $availableOptionIO = [
        'domain'             => Domain::class,
        'https_enabled'      => HttpsEnabled::class,
        'site_user'          => SiteUser::class,
        'db_name'            => DbName::class,
        'db_user'            => DbUser::class,
        'db_pass'            => DbPass::class,
        'wp_title'           => WpTitle::class,
        'wp_admin_user'      => WpAdminUser::class,
        'wp_admin_email'     => WpAdminEmail::class,
        'wp_admin_pass'      => WpAdminPass::class,
        'php_version'        => PhpVersion::class,
        'page_cache_enabled' => PageCacheEnabled::class,
    ];

    protected function action(): int
    {
        if (!in_array($this->argument('installation_method'), OptionsHelper::INSTALLATION_METHODS, true)) {
            $this->error('Invalid site type.');
            $this->newLine(1);
            return self::INVALID;
        }

        $server    = $this->selectServer('deploy to')->first();
        $userInput = $this->getUserInput();
        $site      = $this->spinupwp->createSite($server->id, array_merge($this->arguments(), $this->options(), $userInput));

        $this->successfulStep("{$site->domain} is {$site->status} (event_id = {$site->eventId()})");

        return self::SUCCESS;
    }

    /**
     * Use different options depending on site install type.
     */
    protected function getAvailableOptionIO(string $type): array
    {
        switch ($type) {
            case 'blank':
                return Arr::except($this->availableOptionIO, [
                    'db_name', 'db_user', 'db_pass',
                    'wp_title', 'wp_admin_user', 'wp_admin_email', 'wp_admin_pass',
                ]);
            case 'git':
                return Arr::except($this->availableOptionIO, [
                    'wp_title', 'wp_admin_user', 'wp_admin_email', 'wp_admin_pass',
                ]);
            default:
                return $this->availableOptionIO;
        }
    }

    protected function getUserInput(): array
    {
        $domain    = '';
        $userInput = [];
        $optionIO  = $this->getAvailableOptionIO($this->argument('installation_method'));

        foreach ($optionIO as $optionKey => $optionClass) {
            // skip if option already set
            if (empty($this->option($optionKey))) {
                $optionClass = resolve($optionClass);

                // these options use the domain to "seed" default values
                if ($optionKey === 'site_user' || $optionKey === 'db_name' || $optionKey === 'db_user' || $optionKey === 'wp_title') {
                    $optionClass->default = $domain;
                }

                $userInput[$optionKey] = $this->getOptionValue($optionClass, $this->nonInteractive());
            }

            if ($optionKey === 'domain') {
                $domain = $userInput[$optionKey] ?? $this->option('domain');
            }
        }
        return $userInput;
    }
}
