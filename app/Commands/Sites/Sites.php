<?php

namespace App\Commands\Sites;

use App\Commands\BaseCommand;
use App\Commands\Concerns\SpecifyFields;
use App\Field;

abstract class Sites extends BaseCommand
{
    use SpecifyFields;

    protected function setUp(): void
    {
        $this->fieldsMap = [
            (new Field('ID', 'id')),
            (new Field('Server ID', 'server_id')),
            (new Field('Domain', 'domain')),
            (new Field('Additional Domains', 'additional_domains'))
                ->withTransformRule(fn ($value) => implode(PHP_EOL, array_map(fn ($domain) => $domain['domain'], $value))),
            (new Field('Site User', 'site_user')),
            (new Field('PHP Version', 'php_version')),
            (new Field('Public Folder', 'public_folder')),
            (new Field('Page Cache', 'page_cache'))
                ->couldBeEnabledOrDisabled(),
            (new Field('HTTPS', 'https'))
                ->couldBeEnabledOrDisabled(),
            (new Field('Database Table Prefix', 'database'))
                ->withIgnoreRule(fn ($value)    => empty($value['table_prefix']))
                ->withTransformRule(fn ($value) => $value['table_prefix']),
            (new Field('Git', 'git'))
                ->couldBeEnabledOrDisabled(),
            (new Field('Repository', 'git'))
                ->withAliases(['git.repo'])
                ->withIgnoreRule(fn ($value)    => !$value['enabled'])
                ->withTransformRule(fn ($value) => $value['repository']),
            (new Field('Branch', 'git'))
                ->withAliases(['git.branch'])
                ->withIgnoreRule(fn ($value)    => !$value['enabled'])
                ->withTransformRule(fn ($value) => $value['branch']),
            (new Field('Deploy Script', 'git'))
                ->withAliases(['git.deploy_script'])
                ->withIgnoreRule(fn ($value)    => !$value['enabled'])
                ->withTransformRule(fn ($value) => $value['deploy_script']),
            (new Field('Push-to-deploy', 'git'))
                ->withAliases(['git.push_enabled'])
                ->withIgnoreRule(fn ($value)    => !$value['enabled'] || !$value['push_enabled'])
                ->withTransformRule(fn ($value) => $value['push_enabled'] ? 'Enabled' : 'Disabled'),
            (new Field('Deployment URL', 'git'))
                ->withAliases(['git.deployment_url'])
                ->withIgnoreRule(fn ($value)    => !$value['enabled'] || !$value['push_enabled'])
                ->withTransformRule(fn ($value) => $value['deployment_url']),
            (new Field('WP Core Update', 'wp_core_update'))
                ->yesOrNo(),
            (new Field('WP Theme Updates', 'wp_theme_updates')),
            (new Field('WP Plugin Updates', 'wp_plugin_updates')),
            (new Field('Scheduled Backups', 'backups'))
                ->withAliases(['backups.next_run_time'])
                ->withTransformRule(fn ($value) => (bool) $value['next_run_time'] ? 'Enabled' : 'Disabled'),
            (new Field('File Backups', 'backups'))
                ->withAliases(['backups.files'])
                ->withTransformRule(fn ($value) => $value['files'] ? 'Enabled' : 'Disabled'),
            (new Field('Database Backups', 'backups'))
                ->withAliases(['backups.database'])
                ->withTransformRule(fn ($value) => $value['database'] ? 'Enabled' : 'Disabled'),
            (new Field('Backup Retention Period', 'backups'))
                ->withAliases(['backups.retention_period'])
                ->withIgnoreRule(fn ($value)    => !$value['files'] && $value['database'])
                ->withTransformRule(fn ($value) => $value['retention_period'] . ' days'),
            (new Field('Next Backup Time', 'backups'))
                ->withAliases(['backups.next_run_time'])
                ->withIgnoreRule(fn ($value)    => !(bool) $value['next_run_time'])
                ->withTransformRule(fn ($value) => $value['next_run_time']),
            (new Field('Uploads Directory Protection', 'nginx'))
                ->withAliases(['nginx.uploads_directory_protected'])
                ->withTransformRule(fn ($value) => $value['uploads_directory_protected'] ? 'Enabled' : 'Disabled'),
            (new Field('XML-RPC Protection', 'nginx'))
                ->withAliases(['nginx.xml_rpc_protected'])
                ->withTransformRule(fn ($value) => $value['xml_rpc_protected'] ? 'Enabled' : 'Disabled'),
            (new Field('Mulsite Rewrite Rules', 'nginx'))
                ->withAliases(['nginx.subdirectory_rewrite_in_place'])
                ->withTransformRule(fn ($value) => $value['subdirectory_rewrite_in_place'] ? 'Enabled' : 'Disabled'),
            (new Field('Basic Auth', 'basic_auth'))
                ->couldBeEnabledOrDisabled(),
            (new Field('Basic Auth Username', 'basic_auth'))
                ->withAliases(['basic_auth.username'])
                ->withIgnoreRule(fn ($value)    => !$value['enabled'])
                ->withTransformRule(fn ($value) => $value['username']),
            (new Field('Created At', 'created_at')),
            (new Field('Status', 'status'))
                ->withFirstCharUpperCase(),
        ];
    }
}
