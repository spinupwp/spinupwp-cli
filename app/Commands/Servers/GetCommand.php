<?php

namespace App\Commands\Servers;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasLargeOutput;
use App\Commands\Concerns\SpecifyColumns;
use App\Helpers\Configuration;
use DeliciousBrains\SpinupWp\SpinupWp;

class GetCommand extends BaseCommand
{
    use HasLargeOutput;
    use SpecifyColumns;

    protected $signature = 'servers:get {server_id} {--format=} {--profile=} {--columns=}';

    protected $description = 'Get a server';

    public function __construct(Configuration $configuration, SpinupWp $spinupWp)
    {
        parent::__construct($configuration, $spinupWp);
        $this->largeOutput = true;

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

    public function action()
    {
        $serverId = $this->argument('server_id');

        $server = $this->spinupwp->servers->get($serverId);

        if ($this->displayFormat() === 'json') {
            return $server;
        }

        $this->columnsMaxWidths[] = [1, 50];

        return $this->specifyColumns($server);
    }
}
