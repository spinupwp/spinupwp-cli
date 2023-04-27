<?php

namespace App\Repositories;

use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use SpinupWp\Endpoints\Event;
use SpinupWp\Endpoints\Server;
use SpinupWp\Endpoints\Site;
use SpinupWp\Resources\Server as ServerResource;
use SpinupWp\Resources\Site as SiteResource;
use SpinupWp\SpinupWp;

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

    public function createSite(int $serverId, array $inputParams): SiteResource
    {
        $inputParams = [
            'installation_method' => $inputParams['installation-method'],
            'domain'              => $inputParams['domain'],
            'php_version'         => $inputParams['php-version'],
            'site_user'           => $inputParams['site-user'],
            'page_cache'          => [
                'enabled' => $inputParams['page-cache-enabled'],
            ],
            'https' => [
                'enabled' => $inputParams['https-enabled'],
            ],
            'database' => [
                'name'     => $inputParams['db-name'],
                'username' => $inputParams['db-user'],
                'password' => $inputParams['db-pass'],
            ],
            'wordpress' => [
                'title'          => $inputParams['wp-title'],
                'admin_user'     => $inputParams['wp-admin-user'],
                'admin_email'    => $inputParams['wp-admin-email'],
                'admin_password' => $inputParams['wp-admin-pass'],
            ],
        ];

        return $this->spinupwp->sites->create($serverId, $inputParams);
    }
}
