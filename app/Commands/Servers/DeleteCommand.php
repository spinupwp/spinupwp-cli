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

    protected $simpleOutput = true;

    public function action(): int
    {
        $serverId = $this->argument('server_id');

        if (empty($serverId)) {
            $serverId = $this->askToSelectServer('Which server would you like to delete?');
        }

        $delete = $this->option('force') || $this->confirm('Are you sure you want to delete this server?');

        if ($delete) {
            $response = $this->spinupwp->servers->delete($serverId, (bool) $this->option('delete-on-provider'));
            $this->info("Server deletion in progress. Event ID: {$response}");
        }

        return self::SUCCESS;
    }
}
