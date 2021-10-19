<?php

namespace App\Commands\Servers;

use App\Commands\BaseCommand;

class ListCommand extends BaseCommand
{
    protected $signature = 'servers:list {--format=format}';

    protected $description = 'Retrieve a list or servers';

    protected function action()
    {
        return $this->spinupwp->servers->list();
    }
}
