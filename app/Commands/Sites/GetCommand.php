<?php

namespace App\Commands\Sites;

use App\Commands\Sites\Sites;

class GetCommand extends Sites
{
    protected $signature = 'sites:get
                            {site_id : The site to output}
                            {--format= : The output format (json or table)}
                            {--profile=}
                            {--fields= : The fields to output}';

    protected $description = 'Get a site';

    public function action(): int
    {
        $this->largeOutput = true;
        $siteId            = $this->argument('site_id');
        $site              = $this->spinupwp->getSite((int) $siteId);

        if ($this->shouldSpecifyFields()) {
            $this->saveFieldsFilter();
            $this->format($this->specifyFields($site));
            return self::SUCCESS;
        }

        if ($this->displayFormat() === 'table') {
            $site = $this->specifyFields($site, [
                'id',
                'server_id',
                'domain',
                'site_user',
                'php_version',
                'page_cache',
                'https',
            ]);
        }

        $this->format($site);

        return self::SUCCESS;
    }
}
