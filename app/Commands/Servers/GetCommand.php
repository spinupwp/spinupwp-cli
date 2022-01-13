<?php

namespace App\Commands\Servers;

use App\Commands\BaseCommand;
use App\Commands\Concerns\SpecifyFields;

class GetCommand extends BaseCommand
{
    use SpecifyFields;

    protected $signature = 'servers:get
                            {server_id : The server to output}
                            {--format=}
                            {--profile=}
                            {--fields=}';

    protected $description = 'Get a server';

    protected function setup(): void
    {
        $this->largeOutput = true;
        $this->fieldsMap   = [
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

    public function action(): int
    {
        $serverId = $this->argument('server_id');
        $server   = $this->spinupwp->getServer((int) $serverId);

        if ($this->shouldSpecifyFields()) {
            $this->saveFieldsFilter();
            $this->format($this->specifyFields($server));
            return self::SUCCESS;
        }

        if ($this->displayFormat() === 'table') {
            $server = $this->specifyFields($server, [
                'id',
                'name',
                'ip_address',
                'ubuntu_version',
                'database.server',
            ]);
        }

        $this->format($server);

        return self::SUCCESS;
    }
}
