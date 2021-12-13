<?php

namespace App\Commands\Servers;

use App\Commands\BaseCommand;

class DeleteCommand extends BaseCommand
{
    protected $signature = 'servers:delete {id?} {--force} {--profile=}';

    protected $description = 'Delete a server';

    protected $simpleOutput = true;

    public function action(): int
    {
        $serverId = $this->argument('id');

        $delete = $this->option('force') || $this->confirm('Are you sure you want to delete this server?');

        if ($delete) {
            $response = $this->spinupwp->servers->delete($serverId);
            $this->info("Server deletion in progress. Event ID: {$response}");
        }

        return self::SUCCESS;
    }
}
