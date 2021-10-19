<?php

namespace App\Commands;

use App\Helpers\Configuration;
use DeliciousBrains\SpinupWp\SpinupWp;
use GuzzleHttp\Client as HttpClient;
use LaravelZero\Framework\Commands\Command;

abstract class BaseCommand extends Command
{
    protected Configuration $config;

    protected SpinupWp $spinupwp;

    public function __construct(Configuration $configuration)
    {
        parent::__construct();
        $this->config = $configuration;

        $client = null;

        if (config('app.env') !== 'production') {
            $client = new HttpClient([
                'base_uri'    => config('app.api_url'),
                'http_errors' => false,
                'headers'     => [
                    'Authorization' => "Bearer {$this->apiToken()}",
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                ],
            ]);
        }

        if ($this->config->isConfigured()) {
            $this->spinupwp = new SpinupWp($this->apiToken(), $client);
        }
    }

    public function handle(): int
    {
        $payload = $this->action();
        $this->info($this->format($payload));
        return 0;
    }

    abstract protected function action();

    protected function apiToken(): string
    {
        return $this->config->get('api_token');
    }

    protected function format($resource)
    {
        $format = $this->config->get('format');
        if (!is_string($this->option('format'))) {
            $format = $this->option('format');
        }
        if ($format !== 'json') {
            //
        }
        return $this->toJson($resource);
    }

    protected function toJson($resource): string
    {
        return json_encode($resource->toArray(), JSON_PRETTY_PRINT);
    }
}
