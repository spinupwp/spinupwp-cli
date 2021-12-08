<?php

namespace App\Commands\Servers;

use App\Commands\BaseCommand;
use App\Commands\Concerns\SpecifyFields;

class ListCommand extends BaseCommand
{
    use SpecifyFields;

    protected $signature = 'servers:list
                            {--format=}
                            {--profile=}
                            {--fields=}';

    protected $description = 'Retrieves a list of servers';

    protected function setup(): void
    {
        $this->fieldsMap = [
            'ID'            => 'id',
            'Name'          => 'name',
            'Provider Name' => 'provider_name',
            'IP Address'    => 'ip_address',
            'SSH Port'      => 'ssh_port',
            'Ubuntu'        => 'ubuntu_version',
            'Timezone'      => 'timezone',
            'Region'        => 'region',
            'Size'          => 'size',
            'Disk Space'    => [
                'property' => 'disk_space',
                'filter'   => fn ($value)   => $this->formatBytes($value['used']) . ' of ' . $this->formatBytes($value['total'], 0) . ' used',
            ],
            'Database Server' => [
                'property' => 'database|database.server',
                'filter'   => fn ($value)   => $value['server'],
            ],
            'Database Host' => [
                'property' => 'database|database.host',
                'filter'   => fn ($value)   => $value['host'],
            ],
            'Database Port' => [
                'property' => 'database|database.port',
                'filter'   => fn ($value)   => $value['port'],
            ],
            'SSH Public Key'    => 'ssh_publickey',
            'Git Public Key'    => 'git_publickey',
            'Connection Status' => [
                'property' => 'connection_status',
                'filter'   => fn ($value)   => ucfirst($value),
            ],
            'Reboot Required' => [
                'property' => 'reboot_required',
                'filter'   => fn ($value)   => $value ? 'Yes' : 'No',
            ],
            'Upgrade Required' => [
                'property' => 'upgrade_required',
                'filter'   => fn ($value)   => $value ? 'Yes' : 'No',
            ],
            'Install Notes' => 'install_notes',
            'Created At'    => 'created_at',
            'Status'        => [
                'property' => 'status',
                'filter'   => fn ($value)   => ucfirst($value),
            ],
        ];
    }

    protected function action(): int
    {
        $servers = collect($this->spinupwp->servers->list());

        if ($servers->isEmpty()) {
            $this->warn('No servers found.');
            return self::SUCCESS;
        }

        if ($this->option('fields')) {
            $this->saveFieldsFilter();
            $servers->transform(fn ($server) => $this->specifyFields($server));
            $this->format($servers);
            return self::SUCCESS;
        }

        if ($this->displayFormat() === 'table') {
            $servers->transform(fn ($server) => $this->specifyFields($server, [
                'id',
                'name',
                'ip_address',
                'ubuntu_version',
                'database.server',
            ]));
        }

        $this->format($servers);

        return self::SUCCESS;
    }
}
