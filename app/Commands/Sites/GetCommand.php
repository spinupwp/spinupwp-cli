<?php

namespace App\Commands\Sites;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasLargeOutput;
use App\Commands\Concerns\SpecifyFields;

class GetCommand extends BaseCommand
{
    use HasLargeOutput;
    use SpecifyFields;

    protected $signature = 'sites:get {site_id} {--format=} {--profile=} {--fields=} {--savefields}';

    protected $description = 'Get a site';

    protected array $fieldsMap = [];

    protected function setUp()
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

    public function action()
    {
        $site = $this->spinupwp->sites->get($this->argument('site_id'));

        if ($this->displayFormat() === 'json') {
            return $site;
        }

        if ($this->option('fields')) {
            $this->saveFieldsFilter($this->option('savefields'));
        }

        return $this->specifyFields($site);
    }

    public function nginxData(array $nginxData): array
    {
        return [
            'Uploads Directory Protection' => $nginxData['uploads_directory_protected'] ? 'Enabled' : 'Disabled',
            'XML-RPC Protection'           => $nginxData['xmlrpc_protected'] ? 'Enabled' : 'Disabled',
            'Multisite Rewrite Rules'      => $nginxData['subdirectory_rewrite_in_place'] ? 'Enabled' : 'Disabled',
        ];
    }

    public function backupsData(array $backupsData): array
    {
        $scheduledBackups = (bool) $backupsData['next_run_time'];

        $data['Scheduled Backups'] = $scheduledBackups ? 'Enabled' : 'Disabled';

        $data['File Backups']     = ($backupsData['files'] ? 'Enabled' : 'Disabled');
        $data['Database Backups'] = ($backupsData['database'] ? 'Enabled' : 'Disabled');

        if ($backupsData['files'] || $backupsData['database']) {
            $data['Backup Retention Period'] = $backupsData['retention_period'] . ' days';
        }

        if ($scheduledBackups) {
            $data['Next Backup Time'] = $backupsData['next_run_time'];
        }

        return $data;
    }

    public function gitData(array $gitData): array
    {
        $data['Git'] = 'Disabled';

        if ($gitData['enabled']) {
            $data['Git']            = 'Enabled';
            $data['Repository']     = $gitData['repo'];
            $data['Branch']         = $gitData['branch'];
            $data['Deploy Script']  = $gitData['deploy_script'];
            $data['Push-to-deploy'] = $gitData['push_enabled'] ? 'Enabled' : 'Disabled';
        }

        if ($gitData['enabled'] && $gitData['push_enabled']) {
            $data['Deployment URL'] = $gitData['deployment_url'];
        }

        return $data;
    }

    public function basicAuthData(array $basicAuthData): array
    {
        $data['Basic Auth'] = 'Disabled';

        if ($basicAuthData['enabled']) {
            $data['Basic Auth']          = 'Enabled';
            $data['Basic Auth Username'] = $basicAuthData['username'];
        }

        return $data;
    }
}
