<?php

namespace App\Commands\Servers;

use App\Commands\BaseCommand;
use App\Commands\Concerns\SpecifyColumns;
use App\Helpers\Configuration;
use DeliciousBrains\SpinupWp\SpinupWp;

class ListCommand extends BaseCommand
{
    use SpecifyColumns;

    protected $signature = 'servers:list {--format=} {--profile=} {--columns=}';

    protected $description = 'Retrieves a list of servers';

    public function __construct(Configuration $configuration, SpinupWp $spinupWp)
    {
        parent::__construct($configuration, $spinupWp);

        $this->columnsMap = [
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
                'property' => 'database',
                'filter'   => fn ($value)   => $value['server'],
            ],
            'Database Host' => [
                'property' => 'database',
                'filter'   => fn ($value)   => $value['host'],
            ],
            'Database Port' => [
                'property' => 'database',
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

    protected function action()
    {
        $servers = collect($this->spinupwp->servers->list());

        if ($servers->isEmpty()) {
            $this->warn('No servers found.');
            return $servers;
        }

        if ($this->displayFormat() === 'json') {
            return $servers;
        }

        if ($this->option('columns')) {
            return $servers->map(fn ($server) => $this->specifyColumns($server));
        }

        return $servers->map(fn ($server) => [
            'ID'         => $server->id,
            'Name'       => $server->name,
            'IP Address' => $server->ip_address,
            'Ubuntu'     => $server->ubuntu_version,
            'Database'   => $server->database['server'],
        ]);
    }
}
