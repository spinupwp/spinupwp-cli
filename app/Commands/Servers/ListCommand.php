<?php

namespace App\Commands\Servers;

class ListCommand extends ServerCommand
{
    protected $signature = 'servers:list {--format=} {--profile=}';

    protected $description = 'Retrieve a list or servers';

    protected function action()
    {
        return $this->filterData(
            collect($this->spinupwp->servers->list())
        );
    }
}
