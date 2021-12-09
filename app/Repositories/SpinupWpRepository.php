<?php

namespace App\Repositories;

use DeliciousBrains\SpinupWp\Endpoints\Event;
use DeliciousBrains\SpinupWp\Endpoints\Server;
use DeliciousBrains\SpinupWp\Endpoints\Site;
use DeliciousBrains\SpinupWp\Resources\Server as ServerResource;
use DeliciousBrains\SpinupWp\Resources\Site as SiteResource;
use DeliciousBrains\SpinupWp\SpinupWp;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;

/**
 * @method SpinupWp setApiKey(string $apiKey)
 * @method bool hasApiKey()
 * @method SpinupWp setClient(Client $client)
 * @method Client getClient()
 * @property Event $events
 * @property Server $servers
 * @property Site $sites
 */
class SpinupWpRepository
{
    protected const PAGINATION_LIMIT = 100;

    protected SpinupWp $spinupwp;

    public function __construct(SpinupWp $spinupWp)
    {
        $this->spinupwp = $spinupWp;
    }

    /**
     * @return mixed|void
     */
    public function __call(string $method, array $parameters)
    {
        return $this->spinupwp->{$method}(...$parameters);
    }

    /**
     * @return mixed|void
     */
    public function __get(string $name)
    {
        return $this->spinupwp->{$name};
    }

    public function getServer(int $serverId): ServerResource
    {
        return $this->spinupwp->servers->get($serverId);
    }

    public function listServers(): Collection
    {
        return collect($this->spinupwp->servers->list(1, [
            'limit' => self::PAGINATION_LIMIT,
        ]));
    }

    public function getSite(int $siteId): SiteResource
    {
        return $this->spinupwp->sites->get($siteId);
    }

    public function listSites(int $serverId = null): Collection
    {
        $params = [
            'limit' => self::PAGINATION_LIMIT,
        ];

        if (is_null($serverId)) {
            return collect($this->spinupwp->sites->list(1, $params));
        }

        return collect($this->spinupwp->sites->listForServer($serverId, 1, $params));
    }
}
