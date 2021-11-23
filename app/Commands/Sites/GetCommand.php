<?php

namespace App\Commands\Sites;

use App\Commands\BaseCommand;

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
            $additionalDomains = implode(', ', array_map(fn ($domain) => $domain['domain'], $site->additional_domains));
        }

        $nginx = 'Uploads Directory Protected: ' . ($site->nginx['uploads_directory_protected'] ? 'Yes' : 'No') . ', ';
        $nginx .= 'XMLRPC Protected: ' . ($site->nginx['xmlrpc_protected'] ? 'Yes' : 'No') . ', ';
        $nginx .= 'Subdirectory Rewrite In Place: ' . ($site->nginx['subdirectory_rewrite_in_place'] ? 'Yes' : 'No');

        $database = $site->database['id'] ? 'ID: ' . $site->database['id'] . ', ' . 'User ID: ' . $site->database['user_id'] . ', ' . 'Table Prefix: ' . $site->database['table_prefix'] : 'No Database';

        $backups = 'Disabled';

        if ($site->backups['files'] || $site->backups['database']) {
            $backups = 'Files: ' . ($site->backups['files'] ? 'Yes' : 'No') . ', ';
            $backups .= 'Database: ' . ($site->backups['database'] ? 'Yes' : 'No') . ', ';
            $backups .= 'Retention Period: ' . $site->backups['retention_period'] . ', ';
            $backups .= 'Next Run Time: ' . $site->backups['next_run_time'];
        }

        $git = 'Disabled';

        if ($site->git['enabled']) {
            $git = 'Repo: ' . $site->git['repo'] . ', ';
            $git .= 'Branch: ' . $site->git['branch'] . ', ';
            $git .= 'Deploy Script: ' . $site->git['deploy_script'];
        }

        return [
            'ID'                 => $site->id,
            'Server ID'          => $site->server_id,
            'Domain'             => $site->domain,
            'Additional Domains' => $additionalDomains,
            'Site User'          => $site->site_user,
            'PHP Version'        => $site->php_version,
            'Public Folder'      => $site->public_folder,
            'Is Wordpress'       => $site->is_wordpress ? 'Yes' : 'No',
            'Page Cache'         => $site->page_cache['enabled'] ? 'Enabled' : 'Disabled',
            'HTTPS'              => $site->https['enabled'] ? 'Enabled' : 'Disabled',
            'Nginx'              => $nginx,
            'Database'           => $database,
            'Backups'            => $backups,
            'WP Core Update'     => $site->wp_core_update ? 'Yes' : 'No',
            'WP Theme Updates'   => $site->wp_theme_updates,
            'WP Plugin Updates'  => $site->wp_plugin_updates,
            'Git'                => $git,
            'Basic Auth'         => $site->basic_auth['enabled'] ? 'Enabled' : 'Disabled',
            'Created At'         => $site->created_at,
            'Status'             => ucfirst($site->status),
        ];
    }
}
