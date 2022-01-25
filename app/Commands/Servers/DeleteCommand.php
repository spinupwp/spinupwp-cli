<?php

namespace App\Commands\Servers;

use App\Commands\BaseCommand;

class DeleteCommand extends BaseCommand
{
    protected $signature = 'servers:delete
                            {server_id? : The server to delete}
                            {--d|delete-on-provider : Delete the server from the server provider (DigitalOcean, etc.)}
                            {--f|force : Delete the server without prompting for confirmation}
                            {--profile=}';

    protected $description = 'Delete a server';

    public function action(): int
    {
        $serverId = $this->argument('server_id');

        if (empty($serverId)) {
            $serverId = $this->askToSelectServer('Which server would you like to delete?');
        }

        $server = $this->spinupwp->getServer((int) $serverId);
        $force  = (bool) $this->option('force');

        if (!$force) {
            $this->alert("You're about to delete \"{$server->name}\"");
            $confirmed = $this->confirm('Do you wish to continue?', false);
        }

        if ($force || $confirmed) {
            $eventId = $server->delete((bool) $this->option('delete-on-provider'));

            $this->successfulStep('Server queued for deletion.');

            $this->stepTable([
                'Event ID',
                'Server',
            ], [[
                $eventId,
                $server->name,
            ]]);
        }

        return self::SUCCESS;
    }
}
