<?php

namespace App\Commands\Sites;

use App\Commands\BaseCommand;
use App\Commands\Concerns\InteractsWithIO;

class ListCommand extends BaseCommand
{
    use InteractsWithIO;

    protected $signature = 'sites:list {server_id? : Only list sites belonging to this server} {--format=} {--profile=}';

    protected $description = 'Retrieves a list of sites';

    protected function action(): int
    {
        $serverId = $this->argument('server_id');

        if ($serverId) {
            $sites = collect($this->spinupwp->sites->listForServer((int) $serverId));
        } else {
            $sites = collect($this->spinupwp->sites->list());
        }

        if ($this->displayFormat() === 'table') {
            $sites->transform(fn($site) => [
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
