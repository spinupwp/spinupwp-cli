<?php

namespace App\Commands\Sites;

use App\Commands\BaseCommand;
use DeliciousBrains\SpinupWp\Resources\Site;

class GetCommand extends BaseCommand
{
    protected $signature = 'sites:get
                            {site_id : The site to output}
                            {--format=}
                            {--profile=}';

    protected $description = 'Get a site';

    protected bool $largeOutput = true;

    public function action(): int
    {
        $site = $this->spinupwp->sites->get((int)$this->argument('site_id'));

        if ($this->displayFormat() === 'json') {
            $this->toJson($site);
            return self::SUCCESS;
        }

        $additionalDomains = '';

        if (!empty($site->additional_domains)) {
            $additionalDomains = implode(PHP_EOL, array_map(fn($domain) => $domain['domain'], $site->additional_domains));
        }

        $data = [
            'ID'                 => $site->id,
            'Server ID'          => $site->server_id,
            'Domain'             => $site->domain,
            'Additional Domains' => $additionalDomains,
            'Site User'          => $site->site_user,
            'PHP Version'        => $site->php_version,
            'Public Folder'      => $site->public_folder,
            'Page Cache'         => $site->page_cache['enabled'] ? 'Enabled' : 'Disabled',
            'HTTPS'              => $site->https['enabled'] ? 'Enabled' : 'Disabled',
        ];

        if ($site->database['table_prefix']) {
            $data['Database Table Prefix'] = $site->database['table_prefix'];
        }

        $data = $this->gitData($site, $data);

        $data['WP Core Update']    = $site->wp_core_update ? 'Yes' : 'No';
        $data['WP Theme Updates']  = $site->wp_theme_updates;
        $data['WP Plugin Updates'] = $site->wp_plugin_updates;

        $data = $this->backupsData($site, $data);

        $data['Uploads Directory Protection'] = $site->nginx['uploads_directory_protected'] ? 'Enabled' : 'Disabled';
        $data['XML-RPC Protection']           = $site->nginx['xmlrpc_protected'] ? 'Enabled' : 'Disabled';
        $data['Multisite Rewrite Rules']      = $site->nginx['subdirectory_rewrite_in_place'] ? 'Enabled' : 'Disabled';

        $data = $this->basicAuthData($site, $data);

        $data['Created At'] = $site->created_at;
        $data['Status']     = ucfirst($site->status);

        $this->format($data);

        return self::SUCCESS;
    }

    public function backupsData(Site $site, array $data): array
    {
        $scheduledBackups = (bool)$site->backups['next_run_time'];

        $data['Scheduled Backups'] = $scheduledBackups ? 'Enabled' : 'Disabled';

        $data['File Backups']     = ($site->backups['files'] ? 'Enabled' : 'Disabled');
        $data['Database Backups'] = ($site->backups['database'] ? 'Enabled' : 'Disabled');

        if ($site->backups['files'] || $site->backups['database']) {
            $data['Backup Retention Period'] = $site->backups['retention_period'] . ' days';
        }

        if ($scheduledBackups) {
            $data['Next Backup Time'] = $site->backups['next_run_time'];
        }

        return $data;
    }

    public function gitData(Site $site, array $data): array
    {
        $data['Git'] = 'Disabled';

        if ($site->git['enabled']) {
            $data['Git']            = 'Enabled';
            $data['Repository']     = $site->git['repo'];
            $data['Branch']         = $site->git['branch'];
            $data['Deploy Script']  = $site->git['deploy_script'];
            $data['Push-to-deploy'] = $site->git['push_enabled'] ? 'Enabled' : 'Disabled';
        }

        if ($site->git['enabled'] && $site->git['push_enabled']) {
            $data['Deployment URL'] = $site->git['deployment_url'];
        }

        return $data;
    }

    public function basicAuthData(Site $site, array $data): array
    {
        $data['Basic Auth'] = 'Disabled';

        if ($site->basic_auth['enabled']) {
            $data['Basic Auth']          = 'Enabled';
            $data['Basic Auth Username'] = $site->basic_auth['username'];
        }

        return $data;
    }
}
