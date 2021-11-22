<?php

namespace App\Commands\Servers;

use App\Commands\BaseCommand;

class GetCommand extends BaseCommand
{
    protected $signature = 'servers:get {server_id} {--format=} {--profile=}';

    protected $description = 'Get a server';

    protected bool $largeOutput = true;

    public function action()
    {
        $this->columnsMaxWidths[] = [1, 100];

        $serverId = $this->argument('server_id');

        $server = $this->spinupwp->servers->get($serverId);

        if ($this->displayFormat() === 'json') {
            return $server;
        }

        $diskSpace = number_format($server->disk_space['used'] / 1024 / 1024 / 1024, 2) . ' GB / ' . number_format($server->disk_space['total'] / 1024 / 1024 / 1024, 2) . ' GB';

        return [
            'ID'                => $server->id,
            'Name'              => $server->name,
            'Provider Name'     => $server->provider_name,
            'IP Address'        => $server->ip_address,
            'Ubuntu'            => $server->ubuntu_version,
            'Database Server'   => $server->database['server'],
            'Database Host'     => $server->database['host'],
            'Database Port'     => $server->database['port'],
            'SSH Port'          => $server->ssh_port,
            'Disk Space'        => $diskSpace,
            'Timezone'          => $server->timezone,
            'Region'            => $server->region,
            'Size'              => $server->size,
            'SSH Public Key'    => $server->ssh_publickey,
            'Git Public Key'    => $server->git_publickey,
            'Connection Status' => $server->connection_status === 'connected' ? '<enabled>Connected</enabled>' : '<disabled>Not Connected</disabled>',
            'Reboot Required'   => $server->reboot_required ? 'Yes' : 'No',
            'Upgrade Required'  => $server->upgrade_required ? 'Yes' : 'No',
            'Install Notes'     => $server->install_notes ?? '',
            'Created At'        => $server->created_at,
            'Status'            => $server->status,
        ];
    }
}
