<?php

namespace App\Commands;

use App\Helpers\Configuration;
use DeliciousBrains\SpinupWp\SpinupWp;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

abstract class BaseCommand extends Command
{
    protected Configuration $config;

    protected SpinupWp $spinupwp;

    protected bool $requiresToken = true;

    protected bool $largeOutput = false;

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
            return 1;
        }

        try {
            if (!$this->spinupwp->hasApiKey()) {
                $this->spinupwp->setApiKey($this->apiToken())->setClient();
            }
            // allow to use a different API URL
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

            $this->format($this->action());

            return 0;
        } catch (Exception $e) {
            $this->error($e->getMessage());
            return 1;
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

    protected function format($resource): void
    {
        if (empty($resource) || ($resource instanceof Collection && $resource->isEmpty())) {
            return;
        }

        $this->setStyles();

        if ($this->displayFormat() === 'table' && $this->largeOutput) {
            $this->largeOutput($resource);
            return;
        }

        if ($this->displayFormat() === 'table') {
            $this->toTable($resource);
            return;
        }

        $this->toJson($resource);
    }

    protected function setStyles(): void
    {
        if (!$this->output->getFormatter()->hasStyle('enabled')) {
            $this->output->getFormatter()->setStyle(
                'enabled',
                new OutputFormatterStyle('green'),
            );
        }

        if (!$this->output->getFormatter()->hasStyle('disabled')) {
            $this->output->getFormatter()->setStyle(
                'disabled',
                new OutputFormatterStyle('red'),
            );
        }
    }

    protected function displayFormat(): string
    {
        if (is_string($this->option('format'))) {
            return $this->option('format');
        }
        return $this->config->get('format', $this->profile());
    }

    protected function toJson($resource): void
    {
        if (!is_array($resource)) {
            $resource = $resource->toArray();
        }
        $this->line(json_encode($resource, JSON_PRETTY_PRINT));
    }

    protected function toTable($resource): void
    {
        $tableHeaders = [];

        if ($resource instanceof Collection) {
            $firstElement = $resource->first();

            if (!is_array($firstElement)) {
                $firstElement = $firstElement->toArray();
            }

            $tableHeaders = array_keys($firstElement);

            $rows = [];

            $resource->each(function ($item) use (&$rows) {
                if (!is_array($item)) {
                    $item->toArray();
                }

                $row = array_map(function ($value) {
                    if (is_array($value)) {
                        $value = '';
                    }
                    if (is_bool($value)) {
                        $value = $value ? '<enabled>Y</enabled>' : '<disabled>N</disabled>';
                    }
                    return $value;
                }, array_values($item));

                $rows[] = $row;
            });
        }

        $this->table(
            $tableHeaders,
            $rows,
        );
    }

    protected function largeOutput(array $resource): void
    {
        $table = [];

        foreach ($resource as $key => $value) {
            $table[] = ['<enabled>' . $key . '</enabled>', $value];
        }

        $this->table(
            [],
            $table,
        );
    }

    abstract protected function action();
}
