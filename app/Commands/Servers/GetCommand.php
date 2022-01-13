<?php

namespace App\Commands\Servers;

use App\Commands\BaseCommand;

class GetCommand extends BaseCommand
{
    protected $signature = 'servers:get
                            {server_id : The server to output}
                            {--format=}
                            {--profile=}';

    protected $description = 'Get a server';

    protected bool $largeOutput = true;

    public function action(): int
    {
        $this->columnsMaxWidths[] = [1, 50];

        $serverId = $this->argument('server_id');
        $server   = $this->spinupwp->getServer((int) $serverId);

        if ($this->displayFormat() === 'table') {
            $server = [
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

        $this->format($server);

        return self::SUCCESS;
    }
}
