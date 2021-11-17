<?php

namespace App\Commands\Concerns;

use Illuminate\Support\Collection;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

trait InteractsWithIO
{
    protected function format($resource): void
    {
        $this->setStyles();

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
        $this->line(json_encode($resource->toArray(), JSON_PRETTY_PRINT));
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

        $this->table($tableHeaders, $rows);
    }
}
