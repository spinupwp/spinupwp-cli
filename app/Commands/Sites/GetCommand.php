<?php

namespace App\Commands\Sites;

use App\Commands\BaseCommand;

class GetCommand extends BaseCommand
{
    protected $signature = 'sites:get {site_id} {--format=} {--profile=}';

    protected $description = 'Get a site';

    protected bool $largeOutput = true;

    public function action()
    {
        $site = $this->spinupwp->sites->get($this->argument('site_id'));

        if ($this->displayFormat() === 'json') {
            return $site;
        }
    }
}
