<?php

namespace App\Commands\Sites;

use App\Commands\BaseCommand;

class ListCommand extends BaseCommand
{
    protected $signature = 'sites:list
                            {server_id? : Only list sites belonging to this server}
                            {--format=}
                            {--profile=}';

    protected $description = 'List all sites';

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

        if ($this->displayFormat() === 'table') {
            $sites->transform(fn ($site) => [
                'ID'         => $site->id,
                'Server ID'  => $site->server_id,
                'Domain'     => $site->domain,
                'Site User'  => $site->site_user,
                'PHP'        => $site->php_version,
                'Page Cache' => $site->page_cache['enabled'],
                'HTTPS'      => $site->https['enabled'],
            ]);
        }

        $this->format($sites);

        return self::SUCCESS;
    }
}
