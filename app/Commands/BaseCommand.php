<?php

namespace App\Commands;

use App\Helpers\Configuration;
use DeliciousBrains\SpinupWp\SpinupWp;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Collection;
use LaravelZero\Framework\Commands\Command;
use LucidFrame\Console\ConsoleTable;

abstract class BaseCommand extends Command
{
    protected Configuration $config;

    protected SpinupWp $spinupwp;

    protected bool $requiresToken = true;

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
        if ($this->requiresToken && !$this->config->isConfigured()) {
            $this->error("You must first run 'spinupwp configure' in order to set up your API token.");
            return 1;
        }

        $payload = $this->action();

        $this->info($this->format($payload));

        return 0;
    }

    protected function apiToken(): string
    {
        return $this->config->get('api_token');
    }

    protected function format($resource)
    {
        if ($this->displayFormat() === 'table') {
            return $this->toTable($resource);
        }
        return $this->toJson($resource);
    }

    protected function displayFormat(): string
    {
        if (is_string($this->option('format'))) {
            return $this->option('format');
        }
        return $this->config->get('format');
    }

    protected function toJson($resource): string
    {
        return json_encode($resource->toArray(), JSON_PRETTY_PRINT);
    }

    protected function toTable($resource)
    {
        $table        = new ConsoleTable();
        $tableHeaders = [];

        if ($resource instanceof Collection) {
            $firstElement = $resource->first();

            if (!is_array($firstElement)) {
                $firstElement = $firstElement->toArray();
            }

            $tableHeaders = array_keys($firstElement);

            foreach ($tableHeaders as $header) {
                $table->addHeader($header);
            }

            $resource->each(function ($item) use ($table) {
                $table->addRow();
                if (!is_array($item)) {
                    $item->toArray();
                }
                $row = array_values($item);
                foreach ($row as $value) {
                    if (is_array($value)) {
                        $value = '';
                    }
                    if (is_bool($value)) {
                        $value = $value ? 'yes' : 'no';
                    }
                    $table->addColumn($value);
                }
            });
        }

        return $table->display();
    }

    abstract protected function action();
}
