<?php

namespace App\Repositories;

use App\Helpers\OptionsHelper;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use SpinupWp\Endpoints\Event;
use SpinupWp\Endpoints\Provider;
use SpinupWp\Endpoints\Server;
use SpinupWp\Endpoints\Site;
use SpinupWp\Endpoints\SshKey;
use SpinupWp\Endpoints\StorageProvider;
use SpinupWp\Resources\Event as EventResource;
use SpinupWp\Resources\Server as ServerResource;
use SpinupWp\Resources\Site as SiteResource;
use SpinupWp\SpinupWp;

/**
 * @method SpinupWp setApiKey(string $apiKey)
 * @method bool hasApiKey()
 * @method SpinupWp setClient(Client $client)
 * @method Client getClient()
 * @property Event $events
 * @property Provider $providers
 * @property Server $servers
 * @property Site $sites
 * @property SshKey $sshKeys
 * @property StorageProvider $storageProviders
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

    /**
     * The most recent events, newest first. Only fetches the pages needed for the limit.
     *
     * @return Collection<int, EventResource>
     */
    public function listEvents(int $limit): Collection
    {
        $events = $this->spinupwp->events->list(1, [
            'limit' => min($limit, self::PAGINATION_LIMIT),
        ]);

        return LazyCollection::make(fn () => yield from $events)->take($limit)->values()->collect();
    }

    /**
     * The regions and sizes a server provider offers.
     *
     * @return array<mixed>
     */
    public function providerMetadata(string $provider): array
    {
        return $this->spinupwp->providers->metadata($provider);
    }

    public function getSite(int $siteId): SiteResource
    {
        return $this->spinupwp->sites->get($siteId);
    }

    public function getSshKey(): string
    {
        return $this->spinupwp->sshKeys->get();
    }

    public function listSshKeys(): Collection
    {
        return collect($this->spinupwp->sshKeys->list(1, [
            'limit' => self::PAGINATION_LIMIT,
        ]));
    }

    public function listStorageProviders(): Collection
    {
        return collect($this->spinupwp->storageProviders->list(1, [
            'limit' => self::PAGINATION_LIMIT,
        ]));
    }

    public function listPathRedirects(int $siteId): Collection
    {
        return collect($this->spinupwp->sites->listPathRedirects($siteId, 1, [
            'limit' => self::PAGINATION_LIMIT,
        ]));
    }

    public function listSites(?int $serverId = null): Collection
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
        $params = [
            'installation_method' => $inputParams['installation-method'],
            'domain'              => $inputParams['domain'],
            'php_version'         => $inputParams['php-version'],
            'site_user'           => $inputParams['site-user'],
            'page_cache'          => [
                'enabled' => $inputParams['page-cache-enabled'],
            ],
        ];

        $isWordPress = in_array($inputParams['installation-method'], OptionsHelper::WORDPRESS_INSTALLATION_METHODS, true);

        // The API creates a database whenever database.name is present, even as null, so only send
        // the database for WordPress sites or when a database name is given.
        if ($isWordPress || !empty($inputParams['db-name'])) {
            $params['database'] = [
                'name'     => $inputParams['db-name'],
                'username' => $inputParams['db-user'],
                'password' => $inputParams['db-pass'],
            ];

            if (!empty($inputParams['db-table-prefix'])) {
                $params['database']['table_prefix'] = $inputParams['db-table-prefix'];
            }
        }

        if ($isWordPress) {
            $params['wordpress'] = [
                'title'          => $inputParams['wp-title'],
                'admin_user'     => $inputParams['wp-admin-user'],
                'admin_email'    => $inputParams['wp-admin-email'],
                'admin_password' => $inputParams['wp-admin-pass'],
            ];
        }

        // Optional settings are only sent when given, so the API applies its own defaults.
        if (!empty($inputParams['public-folder'])) {
            $params['public_folder'] = $inputParams['public-folder'];
        }

        if (!empty($inputParams['deploy-script'])) {
            $params['deploy_script'] = $inputParams['deploy-script'];
        }

        if ($inputParams['installation-method'] === 'git') {
            $deployKey = $inputParams['git-deploy-key'] ?? null;

            $params['git'] = array_filter([
                'repo'                     => $inputParams['git-repo'] ?? null,
                'branch'                   => $inputParams['git-branch'] ?? null,
                'push_to_deploy'           => (bool) ($inputParams['push-to-deploy'] ?? false),
                'always_run_deploy_script' => (bool) ($inputParams['always-run-deploy-script'] ?? false),
                'deploy_key_enabled'       => !is_null($deployKey),
                'deploy_key'               => $deployKey,
            ], fn ($value) => !is_null($value));
        }

        return $this->spinupwp->sites->create($serverId, $params);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function createServer(array $payload): ServerResource
    {
        return $this->spinupwp->servers->create($payload);
    }

    /**
     * @param array<string, string|null> $inputParams
     */
    public function createCustomServer(array $inputParams): ServerResource
    {
        return $this->spinupwp->servers->createCustom([
            'provider_name'          => $inputParams['provider-name'],
            'ubuntu_version'         => $inputParams['ubuntu-version'],
            'ip_address'             => $inputParams['ip-address'],
            'ssh_port'               => $inputParams['ssh-port'],
            'username'               => $inputParams['ssh-username'],
            'auth_method'            => $inputParams['ssh-auth-method'],
            'password'               => $inputParams['ssh-password'] ?? null,
            'hostname'               => $inputParams['hostname'],
            'timezone'               => $inputParams['timezone'],
            'post_provision_script'  => $inputParams['post-provision-script'],
            'database_root_password' => $inputParams['database-root-password'],
            'database_provider_id'   => $inputParams['database-provider-id'],
        ]);
    }
}
