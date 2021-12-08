<?php

namespace App\Commands\Sites;

use App\Commands\BaseCommand;
use App\Commands\Concerns\SpecifyFields;

class ListCommand extends BaseCommand
{
    use SpecifyFields;

    protected $signature = 'sites:list
                            {server_id? : Only list sites belonging to this server}
                            {--format=}
                            {--profile=}';

    protected $description = 'Retrieves a list of sites';

    protected function setup(): void
    {
        $this->fieldsMap = [
            'ID'                 => 'id',
            'Server ID'          => 'server_id',
            'Domain'             => 'domain',
            'Additional Domains' => [
                'property' => 'additional_domains',
                'filter'   => fn ($value)   => implode(PHP_EOL, array_map(fn ($domain) => $domain['domain'], $value)),
            ],
            'Site User'     => 'site_user',
            'PHP Version'   => 'php_version',
            'Public Folder' => 'public_folder',
            'Page Cache'    => [
                'property' => 'page_cache',
                'filter'   => fn ($value)   => $value['enabled'] ? 'Enabled' : 'Disabled',
            ],
            'HTTPS' => [
                'property' => 'https',
                'filter'   => fn ($value)   => $value['enabled'] ? 'Enabled' : 'Disabled',
            ],
            'Database Table Prefix' => [
                'property' => 'database',
                'ignore'   => fn ($value)   => empty($value['table_prefix']),
                'filter'   => fn ($value)   => $value['table_prefix'],

            ],
            'Git' => [
                'property' => 'git',
                'filter'   => fn ($value)   => $this->gitData($value),
            ],
            'WP Core Update' => [
                'property' => 'wp_core_update',
                'filter'   => fn ($value)   => $value ? 'Yes' : 'No',
            ],
            'WP Theme Updates'  => 'wp_theme_updates',
            'WP Plugin Updates' => 'wp_plugin_updates',
            'Backups'           => [
                'property' => 'backups',
                'filter'   => fn ($value)   => $this->backupsData($value),
            ],
            'Nginx' => [
                'property' => 'nginx',
                'filter'   => fn ($value)   => $this->nginxData($value),
            ],
            'Basic Auth' => [
                'property' => 'basic_auth',
                'filter'   => fn ($value)   => $this->basicAuthData($value),
            ],
            'Created At' => 'created_at',
            'Status'     => [
                'property' => 'status',
                'filter'   => fn ($value)   => ucfirst($value),
            ],
        ];
    }

    protected function action(): int
    {
        $serverId = $this->argument('server_id');

        if ($serverId) {
            $sites = collect($this->spinupwp->sites->listForServer((int) $serverId));
        } else {
            $sites = collect($this->spinupwp->sites->list());
        }

        if ($sites->isEmpty()) {
            $this->warn('No sites found.');
            return self::SUCCESS;
        }

        if ($this->option('fields')) {
            $this->saveFieldsFilter();
            return $sites->map(fn ($site) => $this->specifyFields($site));
        }

        if ($this->displayFormat() === 'table') {
            $sites->transform(
                fn ($site) => $this->specifyFields($site, [
                    'id',
                    'server_id',
                    'domain',
                    'site_user',
                    'php_version',
                    'page_cache',
                    'https',
                ])
            );
        }

        $this->format($sites);

        return self::SUCCESS;
    }
}
