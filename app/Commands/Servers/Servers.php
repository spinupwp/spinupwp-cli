<?php

namespace App\Commands\Servers;

use App\Commands\BaseCommand;
use App\Commands\Concerns\SpecifyFields;
use App\Field;

abstract class Servers extends BaseCommand
{
    use SpecifyFields;

    protected function setup(): void
    {
        $this->fieldsMap = [
            (new Field('ID', 'id')),
            (new Field('Name', 'name')),
            (new Field('Provider Name', 'provider_name')),
            (new Field('IP Address', 'ip_address')),
            (new Field('SSH Port', 'ssh_port')),
            (new Field('Ubuntu', 'ubuntu_version')),
            (new Field('Timezone', 'timezone')),
            (new Field('Region', 'region')),
            (new Field('Size', 'size')),
            (new Field('Disk Space', 'disk_space'))
                ->withTransformRule(fn ($value) => $this->formatBytes($value['used']) . ' of ' . $this->formatBytes($value['total'], 0) . ' used'),
            (new Field('Database Server', 'database'))
                ->withAliases(['database.server'])
                ->withTransformRule(fn ($value) => $value['server']),
            (new Field('Database Host', 'database'))
                ->withAliases(['database.host'])
                ->withTransformRule(fn ($value) => $value['host']),
            (new Field('Database Port', 'database'))
                ->withAliases(['database.port'])
                ->withTransformRule(fn ($value) => $value['port']),
            (new Field('SSH Public Key', 'ssh_publickey')),
            (new Field('Git Public Key', 'git_publickey')),
            (new Field('Connection Status', 'connection_status'))
                ->withTransformRule(fn ($value) => ucfirst($value)),
            (new Field('Reboot Required', 'reboot_required'))
                ->yesOrNo(),
            (new Field('Upgrade Required', 'upgrade_required'))
                ->yesOrNo(),
            (new Field('Install Notes', 'install_notes')),
            (new Field('Created At', 'created_at')),
            (new Field('Status', 'status'))
                ->withFirstCharUpperCase(),
        ];
    }
}
