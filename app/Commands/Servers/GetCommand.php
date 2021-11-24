<?php

namespace App\Commands\Servers;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasLargeOutput;
use App\Helpers\Configuration;
use DeliciousBrains\SpinupWp\SpinupWp;

class GetCommand extends BaseCommand
{
    use HasLargeOutput;

    protected $signature = 'servers:get {server_id} {--format=} {--profile=}';

    protected $description = 'Get a server';

    public function __construct(Configuration $configuration, SpinupWp $spinupWp)
    {
        parent::__construct($configuration, $spinupWp);
        $this->largeOutput = true;
    }

    public function action()
    {
        $this->columnsMaxWidths[] = [1, 50];

        $serverId = $this->argument('server_id');

        $server = $this->spinupwp->servers->get($serverId);

        if ($this->displayFormat() === 'json') {
            return $server;
        }

        return [
            'ID'                => $server->id,
            'Name'              => $server->name,
            'Provider Name'     => $server->provider_name,
            'IP Address'        => $server->ip_address,
            'SSH Port'          => $server->ssh_port,
            'Ubuntu'            => $server->ubuntu_version,
            'Timezone'          => $server->timezone,
            'Region'            => $server->region,
            'Size'              => $server->size,
            'Disk Space'        => $this->formatBytes($server->disk_space['used']) . ' of ' . $this->formatBytes($server->disk_space['total'], 0) . ' used',
            'Database Server'   => $server->database['server'],
            'Database Host'     => $server->database['host'],
            'Database Port'     => $server->database['port'],
            'SSH Public Key'    => $server->ssh_publickey,
            'Git Public Key'    => $server->git_publickey,
            'Connection Status' => ucfirst($server->connection_status),
            'Reboot Required'   => $server->reboot_required ? 'Yes' : 'No',
            'Upgrade Required'  => $server->upgrade_required ? 'Yes' : 'No',
            'Install Notes'     => $server->install_notes ?? '',
            'Created At'        => $server->created_at,
            'Status'            => ucfirst($server->status),
        ];
    }
}
