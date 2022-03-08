<?php

namespace App\Commands\Sites;

use App\Commands\Sites\Sites;

class ListCommand extends Sites
{
    protected $signature = 'sites:list
                            {server_id? : Only list sites belonging to this server}
                            {--format= : The output format (json or table)}
                            {--profile=}
                            {--fields=}';

    protected $description = 'Retrieves a list of sites';

    protected function action(): int
    {
        $serverId = $this->argument('server_id');

        if ($serverId) {
            $sites = $this->spinupwp->listSites((int) $serverId);
        } else {
            $sites = $this->spinupwp->listSites();
        }

        if ($sites->isEmpty()) {
            $this->warn('No sites found.');
            return self::SUCCESS;
        }

        if ($this->shouldSpecifyFields()) {
            $this->saveFieldsFilter();
            $sites->transform(fn ($site) => $this->specifyFields($site));
            $this->format($sites);
            return self::SUCCESS;
        }

        if ($this->displayFormat() === 'table') {
            $sites->transform(
                fn ($site) => $this->specifyFields($site, [
                    'id',
                    'server_id',
                    'domain',
                    'site_user',
                    'php_version',
                    'page_cache',
                    'https',
                ])
            );
        }

        $this->format($sites);

        return self::SUCCESS;
    }
}
