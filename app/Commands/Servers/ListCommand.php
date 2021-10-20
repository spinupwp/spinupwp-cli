<?php

namespace App\Commands\Servers;

use App\Commands\BaseCommand;

class ListCommand extends ServerCommand
{
    protected $signature = 'servers:list {--format=}';

    protected $description = 'Retrieve a list or servers';

    protected function action()
    {
        return $this->filterData(
            collect($this->spinupwp->servers->list())
        );
    }
}
