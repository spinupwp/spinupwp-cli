<?php

namespace App\Commands\Sites;

use App\Commands\BaseCommand;

class ListCommand extends BaseCommand
{
    protected $signature = 'sites:list {server_id? : Only list sites belonging to this server} {--format=} {--profile=}';

    protected $description = 'Retrieves a list of sites';

    protected function action()
    {
        $serverId = $this->argument('server_id');

        if ($serverId) {
            $sites = collect($this->spinupwp->sites->listForServer((int) $serverId));
        } else {
            $sites = collect($this->spinupwp->sites->list());
        }

        if ($sites->isEmpty()) {
            $this->warn('No sites found.');
            return $sites;
        }

        if ($this->displayFormat() === 'json') {
            return $sites;
        }

        return $sites->map(fn ($site) => [
            'ID'         => $site->id,
            'Server ID'  => $site->server_id,
            'Domain'     => $site->domain,
            'Site User'  => $site->site_user,
            'PHP'        => $site->php_version,
            'Page Cache' => $site->page_cache['enabled'],
            'HTTPS'      => $site->https['enabled'],
        ]);
    }
}
