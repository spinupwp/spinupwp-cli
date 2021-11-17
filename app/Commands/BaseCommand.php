<?php

namespace App\Commands;

use App\Helpers\Configuration;
use DeliciousBrains\SpinupWp\SpinupWp;
use Exception;
use GuzzleHttp\Client;
use LaravelZero\Framework\Commands\Command;

abstract class BaseCommand extends Command
{
    protected Configuration $config;

    protected SpinupWp $spinupwp;

    protected bool $requiresToken = true;

    public function __construct(Configuration $configuration, SpinupWp $spinupWp)
    {
        parent::__construct();

        $this->config   = $configuration;
        $this->spinupwp = $spinupWp;
    }

    public function handle(): int
    {
        if ($this->requiresToken && !$this->config->isConfigured()) {
            $this->error("You must first run 'spinupwp configure' in order to set up your API token.");
            return self::FAILURE;
        }

        try {
            if (!$this->spinupwp->hasApiKey()) {
                $this->spinupwp->setApiKey($this->apiToken())->setClient();
            }

            // Allow to use a different API URL
            if (!empty($this->config->get('api_url', $this->profile()))) {
                $this->spinupwp->setClient(
                    new Client([
                        'base_uri'    => $this->config->get('api_url', $this->profile()),
                        'http_errors' => false,
                        'headers'     => [
                            'Authorization' => "Bearer {$this->config->get('api_token', $this->profile())}",
                            'Accept'        => 'application/json',
                            'Content-Type'  => 'application/json',
                        ],
                    ])
                );
            }

            return $this->action();
        } catch (Exception $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    protected function apiToken(): string
    {
        $apiToken = $this->config->get('api_token', $this->profile());

        if (!$apiToken) {
            throw new Exception("The API token for the profile {$this->profile()} is not yet configured");
        }

        return $apiToken;
    }

    protected function profile(): string
    {
        if (is_string($this->option('profile'))) {
            return $this->option('profile');
        }

        return 'default';
    }

    abstract protected function action(): int;
}
