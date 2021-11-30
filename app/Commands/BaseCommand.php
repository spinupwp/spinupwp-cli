<?php

namespace App\Commands;

use App\Helpers\Configuration;
use DeliciousBrains\SpinupWp\SpinupWp;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;

abstract class BaseCommand extends Command
{
    protected Configuration $config;

    protected SpinupWp $spinupwp;

    protected bool $requiresToken = true;

    protected bool $largeOutput = false;

    protected array $columnsMaxWidths = [];

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
            $clientOptions = [
                'base_uri'    => $this->config->get('api_url', $this->profile(), 'https://api.spinupwp.app/v1/'),
                'http_errors' => false,
                'headers'     => [
                    'Authorization' => "Bearer {$this->apiToken()}",
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                    'User-Agent'    => 'SpinupWP/' . config('app.version'),
                ],
            ];

            $this->spinupwp->setClient(new Client($clientOptions));

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
        $table = new Table($this->output);
        $rows  = [];

        foreach ($resource as $key => $value) {
            $rows[] = ['<info>' . $key . '</info>', $value];
        }

        $table->setRows($rows)->setStyle('default');

        if (!empty($this->columnsMaxWidths)) {
            foreach ($this->columnsMaxWidths as $column) {
                $table->setColumnMaxWidth($column[0], $column[1]);
            }
        }

        $table->render();
    }

    protected function formatBytes(int $bytes, int $precision = 1, bool $trueSize = false): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $block = ($trueSize) ? 1024 : 1000;

        $bytes = max($bytes, 0);
        $pow   = floor(($bytes ? log($bytes) : 0) / log($block));
        $pow   = min($pow, count($units) - 1);
        $bytes /= pow($block, $pow);

        $total = ($trueSize || $precision > 0) ? round($bytes, $precision) : floor($bytes);

        return $total . ' ' . $units[$pow];
    }

    abstract protected function action();
}
