<?php

namespace App\Commands\Sites;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasLargeOutput;
use App\Commands\Concerns\SpecifyFields;

class GetCommand extends BaseCommand
{
    use SpecifyFields;

    protected $signature = 'sites:get
                            {site_id : The site to output}
                            {--format=}
                            {--profile=}
                            {--fields=}';

    protected $description = 'Get a site';

    protected function setUp(): void
    {
        $this->largeOutput = true;
        $this->fieldsMap   = [
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
                'filter'   => fn ($value)   => $value['enabled'] ? 'Enabled' : 'Disabled',
            ],
            'Repository' => [
                'ignore'   => fn ($value)   => !$value['enabled'],
                'property' => 'git|git.repo',
                'filter'   => fn ($value)   => $value['repo'],
            ],
            'Branch' => [
                'ignore'   => fn ($value)   => !$value['enabled'],
                'property' => 'git|git.branch',
                'filter'   => fn ($value)   => $value['branch'],
            ],
            'Deploy Script' => [
                'ignore'   => fn ($value)   => !$value['enabled'],
                'property' => 'git|git.deploy_script',
                'filter'   => fn ($value)   => $value['deploy_script'],
            ],
            'Push-to-deploy' => [
                'ignore'   => fn ($value)   => !$value['enabled'],
                'property' => 'git|git.push_enabled',
                'filter'   => fn ($value)   => $value['push_enabled'] ? 'Enabled' : 'Disabled',
            ],
            'Deployment URL' => [
                'ignore'   => fn ($value)   => !$value['enabled'] || !$value['push_enabled'],
                'property' => 'git|git.deployment_url',
                'filter'   => fn ($value)   => $value['deployment_url'],
            ],
            'WP Core Update' => [
                'property' => 'wp_core_update',
                'filter'   => fn ($value)   => $value ? 'Yes' : 'No',
            ],
            'WP Theme Updates'  => 'wp_theme_updates',
            'WP Plugin Updates' => 'wp_plugin_updates',
            'Scheduled Backups' => [
                'property' => 'backups|backups.next_run_time',
                'filter'   => fn ($value)   => (bool) $value['next_run_time'] ? 'Enabled' : 'Disabled',
            ],
            'File Backups' => [
                'property' => 'backups|backups.files',
                'filter'   => fn ($value)   => $value['files'] ? 'Enabled' : 'Disabled',
            ],
            'Database Backups' => [
                'property' => 'backups|backups.database',
                'filter'   => fn ($value)   => $value['database'] ? 'Enabled' : 'Disabled',
            ],
            'Backup Retention Period' => [
                'ignore'   => fn ($value)   => !$value['files'] && $value['database'],
                'property' => 'backups|backups.retention_period',
                'filter'   => fn ($value)   => $value['retention_period'] . ' days',
            ],
            'Next Backup Time' => [
                'ignore'   => fn ($value)   => !(bool) $value['next_run_time'],
                'property' => 'backups|backups.next_run_time',
                'filter'   => fn ($value)   => $value['next_run_time'],
            ],
            'Uploads Directory Protection' => [
                'property' => 'nginx|nginx.uploads_directory_protected',
                'filter'   => fn ($value)   => $value['uploads_directory_protected'] ? 'Enabled' : 'Disabled',
            ],
            'XML-RPC Protection' => [
                'property' => 'nginx|nginx.xmlrpc_protected',
                'filter'   => fn ($value)   => $value['xmlrpc_protected'] ? 'Enabled' : 'Disabled',
            ],
            'Multisite Rewrite Rules' => [
                'property' => 'nginx|nginx.subdirectory_rewrite_in_place',
                'filter'   => fn ($value)   => $value['subdirectory_rewrite_in_place'] ? 'Enabled' : 'Disabled',
            ],
            'Basic Auth' => [
                'property' => 'basic_auth',
                'filter'   => fn ($value)   => $value['enabled'] ? 'Enabled' : 'Disabled',
            ],
            'Basic Auth Username' => [
                'ignore'   => fn ($value)   => !$value['enabled'],
                'property' => 'basic_auth|basic_auth.username',
                'filter'   => fn ($value)   => $value['username'],
            ],
            'Created At' => 'created_at',
            'Status'     => [
                'property' => 'status',
                'filter'   => fn ($value)   => ucfirst($value),
            ],
        ];
    }

    public function action(): int
    {
        $site = $this->spinupwp->sites->get((int) $this->argument('site_id'));

        if ($this->displayFormat() === 'json') {
            $this->toJson($site);
            return self::SUCCESS;
        }

        if ($this->option('fields')) {
            $this->saveFieldsFilter();
        }

        $site = $this->specifyFields($site);
        $this->format($site);

        return self::SUCCESS;
    }
}
