<?php

namespace App\Repositories;

use DeliciousBrains\SpinupWp\Endpoints\Event;
use DeliciousBrains\SpinupWp\Endpoints\Server;
use DeliciousBrains\SpinupWp\Endpoints\Site;
use DeliciousBrains\SpinupWp\SpinupWp;
use GuzzleHttp\Client;

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
}
