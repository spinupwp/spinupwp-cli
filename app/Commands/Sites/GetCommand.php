<?php

namespace App\Commands\Sites;

use App\Commands\BaseCommand;
use DeliciousBrains\SpinupWp\Resources\Site;

class GetCommand extends BaseCommand
{
    protected $signature = 'sites:get {site_id} {--format=} {--profile=}';

    protected $description = 'Get a site';

    protected bool $largeOutput = true;

    public function action()
    {
        $site = $this->spinupwp->sites->get($this->argument('site_id'));

        if ($this->displayFormat() === 'json') {
            return $site;
        }
        $additionalDomains = '';

        if (!empty($site->additional_domains)) {
            $additionalDomains = implode(PHP_EOL, array_map(fn ($domain) => $domain['domain'], $site->additional_domains));
        }

        return array_merge(
            [
                'ID'                          => $site->id,
                'Server ID'                   => $site->server_id,
                'Domain'                      => $site->domain,
                'Additional Domains'          => $additionalDomains,
                'Site User'                   => $site->site_user,
                'PHP Version'                 => $site->php_version,
                'Public Folder'               => $site->public_folder,
                'Uploads Directory Protected' => $site->nginx['uploads_directory_protected'] ? 'Enabled' : 'Disabled',
                'XML-RPC Protection'          => $site->nginx['xmlrpc_protected'] ? 'Enabled' : 'Disabled',
                'Multisite Rewrite Rules'     => $site->nginx['subdirectory_rewrite_in_place'] ? 'Enabled' : 'Disabled',
                'Page Cache'                  => $site->page_cache['enabled'] ? 'Enabled' : 'Disabled',
                'HTTPS'                       => $site->https['enabled'] ? 'Enabled' : 'Disabled',
                'Database Table Prefix'       => $site->database['table_prefix'] ?: 'No Database',
                'WP Core Update'              => $site->wp_core_update ? 'Yes' : 'No',
                'WP Theme Updates'            => $site->wp_theme_updates,
                'WP Plugin Updates'           => $site->wp_plugin_updates,
                'Basic Auth'                  => $site->basic_auth['enabled'] ? 'Enabled' : 'Disabled',
                'Created At'                  => $site->created_at,
                'Status'                      => ucfirst($site->status),
            ],
            $this->gitData($site),
            $this->backupsData($site),
        );
    }

    public function backupsData(Site $site): array
    {
        $backups = ['Scheduled Backupś' => 'Disabled'];

        if ($site->backups['files'] || $site->backups['database']) {
            $backups['Scheduled Backupś'] = 'Disabled';
            if ($site->backups['next_run_time']) {
                $backups['Scheduled Backupś'] = 'Enabled';
                $backups['Next Run Time']     = $site->backups['next_run_time'];
            }
            $backups['File backups']     = ($site->backups['files'] ? 'Enabled' : 'Disabled');
            $backups['Database Backups'] = ($site->backups['database'] ? 'Enabled' : 'Disabled');
            $backups['Retention Period'] = $site->backups['retention_period'];
        }

        return $backups;
    }

    public function gitData(Site $site): array
    {
        $git = ['Git' => 'Disabled'];

        if ($site->git['enabled']) {
            $git['Git']            = 'Enabled';
            $git['Repository']     = $site->git['repo'];
            $git['Branch']         = $site->git['branch'];
            $git['Deploy Script']  = $site->git['deploy_script'];
            $git['Push-to-deploy'] = $site->git['push_enabled'] ? 'Enabled' : 'Disabled';
        }

        if ($site->git['enabled'] && $site->git['push_enabled']) {
            $git['Deployment URl'] = $site->git['deployment_url'];
        }

        return $git;
    }
}
