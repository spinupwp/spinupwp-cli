<?php

namespace App\Repositories;

use App\Helpers\OptionsHelper;
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

    public function createSite(int $serverId, array $inputParams): SiteResource
    {
        $inputParams = [
            'installation_method' => $inputParams['installation_method'],
            'domain'              => $inputParams['domain'],
            'php_version'         => OptionsHelper::PHP_VERSIONS[$inputParams['php_version']],
            'site_user'           => $inputParams['site_user'],
            'page_cache'          => [
                'enabled' => $inputParams['page_cache_enabled'],
            ],
            'https' => [
                'enabled' => $inputParams['https_enabled'],
            ],
            'database' => [
                'name'     => $inputParams['db_name'],
                'username' => $inputParams['db_user'],
                'password' => $inputParams['db_pass'],
            ],
            'wordpress' => [
                'title'          => $inputParams['wp_title'],
                'admin_user'     => $inputParams['wp_admin_user'],
                'admin_email'    => $inputParams['wp_admin_email'],
                'admin_password' => $inputParams['wp_admin_pass'],
            ],
        ];

        return $this->spinupwp->sites->create($serverId, $inputParams);
    }
}
