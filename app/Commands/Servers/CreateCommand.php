<?php

namespace App\Commands\Servers;

use App\Commands\Concerns\ParsesOptions;
use App\Commands\Concerns\ReadsDeployKeys;
use App\Commands\Providers\Providers;
use App\Helpers\OptionsHelper;
use Exception;

class CreateCommand extends Providers
{
    use ParsesOptions;
    use ReadsDeployKeys;

    protected $signature = 'servers:create
                            {--provider= : The server provider (digitalocean, hetzner, linode or vultr)}
                            {--provider-id= : The ID of a server provider account saved in SpinupWP}
                            {--api-token= : An API token for the provider, when not using a saved provider account}
                            {--region= : The region slug (see providers:regions)}
                            {--size= : The size slug (see providers:sizes)}
                            {--enable-backups : Enable the provider\'s server backups, which the provider charges extra for}
                            {--hostname= : The server hostname}
                            {--timezone= : The server timezone (defaults to UTC)}
                            {--database-root-password= : Root password for the database (randomly generated if not set)}
                            {--database-provider-id= : ID of an external database saved in SpinupWP}
                            {--post-provision-script= : Path to a script to run as root after provisioning}
                            {--f|force : Provision the server without prompting}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'Provision a server on DigitalOcean, Hetzner, Linode or Vultr';

    protected array $validationLabels = [
        'server_provider.id'        => 'Provider ID',
        'server_provider.name'      => 'Provider',
        'server_provider.api_token' => 'API Token',
        'server_provider.region'    => 'Region',
        'server_provider.size'      => 'Size',
        'database.root_password'    => 'Database Root Password',
        'database_provider.id'      => 'Database Provider ID',
    ];

    protected function action(): int
    {
        $provider = $this->stringOption('provider');

        if (is_null($provider) && $this->nonInteractive()) {
            throw new Exception('The --provider option is required when running with --force.');
        }

        $provider       = $this->selectProvider($provider);
        $serverProvider = $this->providerCredentials($provider);

        $region = $this->stringOption('region');
        $size   = $this->stringOption('size');

        if (is_null($region) || is_null($size)) {
            if ($this->nonInteractive()) {
                throw new Exception('The --region and --size options are required when running with --force.');
            }

            $metadata = $this->spinupwp->providerMetadata($provider);
            $region ??= $this->askForRegion($metadata);
            $size ??= $this->askForSize($metadata, $region);
        }

        $hostname = $this->stringOption('hostname') ?? ($this->nonInteractive() ? null : $this->ask('Hostname'));
        $timezone = $this->stringOption('timezone') ?? ($this->nonInteractive() ? 'UTC' : $this->ask('Timezone', 'UTC'));
        $backups  = $this->option('enable-backups') || (!$this->nonInteractive() && $this->confirm(OptionsHelper::SERVER_PROVIDERS[$provider] . ' backups cost extra. Enable them?', false));

        $payload = [
            'server_provider' => array_merge($serverProvider, [
                'region'         => $region,
                'size'           => $size,
                'enable_backups' => $backups,
            ]),
            'hostname' => $hostname,
            'timezone' => $timezone,
        ];

        if ($rootPassword = $this->stringOption('database-root-password')) {
            $payload['database'] = ['root_password' => $rootPassword];
        }

        if ($databaseProviderId = $this->intOption('database-provider-id')) {
            $payload['database_provider'] = ['id' => $databaseProviderId];
        }

        if ($script = $this->stringOption('post-provision-script')) {
            $payload['post_provision_script'] = $this->readKeyFile($script);
        }

        $providerName = OptionsHelper::SERVER_PROVIDERS[$provider];

        if (!$this->forceOrConfirm("Provision a {$size} server in {$region} on {$providerName}? {$providerName} will bill you for it.")) {
            return self::SUCCESS;
        }

        $server = $this->spinupwp->createServer($payload);

        $this->successfulStep('Server queued for provisioning.');

        $this->stepTable(['Event ID', 'Server ID', 'Name'], [[$server->eventId(), $server->id, $server->name]]);

        return self::SUCCESS;
    }

    /**
     * A saved provider account by ID, or the provider name with an API token.
     *
     * @return array<string, int|string>
     */
    protected function providerCredentials(string $provider): array
    {
        $providerId = $this->intOption('provider-id');

        if (!is_null($providerId)) {
            return ['id' => $providerId];
        }

        $apiToken = $this->stringOption('api-token');

        if (is_null($apiToken) && !$this->nonInteractive()) {
            $apiToken = $this->secret(OptionsHelper::SERVER_PROVIDERS[$provider] . ' API token (or pass --provider-id to use a provider account saved in SpinupWP)');
        }

        if (!is_string($apiToken) || $apiToken === '') {
            throw new Exception('Pass --provider-id to use a provider account saved in SpinupWP, or --api-token.');
        }

        return ['name' => $provider, 'api_token' => $apiToken];
    }

    /**
     * @param array<mixed> $metadata
     */
    protected function askForRegion(array $metadata): string
    {
        $regions = collect($this->regions($metadata))
            ->filter(fn ($region) => $region['available'])
            ->mapWithKeys(fn ($region) => [$region['slug'] => $region['continent'] === '' ? $region['name'] : "{$region['name']} ({$region['continent']})"]);

        if ($regions->isEmpty()) {
            throw new Exception('No regions are available for this provider.');
        }

        $choice = $this->choice('Which region', $regions->all());

        return is_string($choice) ? $choice : '';
    }

    /**
     * @param array<mixed> $metadata
     */
    protected function askForSize(array $metadata, string $region): string
    {
        $sizes = collect($this->sizes($metadata, $region))
            ->filter(fn ($size) => !empty($size['available']))
            ->mapWithKeys(fn ($size) => [$size['slug'] => $this->describeSize($size)]);

        if ($sizes->isEmpty()) {
            throw new Exception("No sizes are available in {$region}.");
        }

        $choice = $this->choice('Which size', $sizes->all());

        return is_string($choice) ? $choice : '';
    }
}
