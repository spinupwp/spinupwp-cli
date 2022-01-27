<?php

namespace App\Commands\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Question\ChoiceQuestion;

trait InteractsWithIO
{
    /**
     * @param mixed $resource
     */
    protected function format($resource): void
    {
        if (empty($resource) || ($resource instanceof Collection && $resource->isEmpty())) {
            return;
        }

        $this->setStyles();

        if ($this->largeOutput && $this->displayFormat() === 'table') {
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

        return (string) $this->config->get('format', $this->profile());
    }

    /**
     * @param mixed $resource
     */
    protected function toJson($resource): void
    {
        $this->line((string) json_encode($resource->toArray(), JSON_PRETTY_PRINT));
    }

    /**
     * @param mixed $resource
     */
    protected function toTable($resource): void
    {
        $rows         = [];
        $tableHeaders = [];

        if ($resource instanceof Collection) {
            $firstElement = $resource->first();

            if (!is_array($firstElement)) {
                $firstElement = $firstElement->toArray();
            }

            $tableHeaders = array_keys($firstElement);

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

    public function askToSelectSite(string $question, callable $filter = null): int
    {
        $choices = collect($this->spinupwp->listSites());

        if (!is_null($filter)) {
            $choices = $choices->filter(fn ($site) => $filter($site));
        }

        return $this->askToSelect(
            $question,
            $choices->keyBy('id')->map(fn ($site) => $site->domain)->toArray()
        );
    }

    /**
     * @param mixed $default
     */
    protected function askToSelect(string $question, array $choices, $default = null): int
    {
        $question = new class($question, $choices, $default) extends ChoiceQuestion {
            public function isAssoc(array $array): bool
            {
                return true;
            }
        };

        return (int) $this->output->askQuestion($question);
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

    public function step(string $text): void
    {
        $this->line("<fg=blue>==></> <options=bold>{$text}</>");
    }

    public function successfulStep(string $text): void
    {
        $this->line("<fg=green>==></> <options=bold>{$text}</>");
    }

    protected function stepTable(array $headers, array $rows): void
    {
        $this->table(
            collect($headers)->map(function ($header) {
                return "   <comment>$header</comment>";
            })->all(),
            collect($rows)->map(function ($row) {
                return collect($row)->map(function ($cell) {
                    return "   <options=bold>$cell</>";
                })->all();
            })->all(),
            'compact',
        );
    }

    protected function forceOrConfirm(string $confirmation, ?bool $default = true): bool
    {
        return (bool) $this->option('force') || $this->confirm($confirmation, $default);
    }

    public function queueResources(Collection $resources, string $endpoint, string $verb): void
    {
        if ($resources->isEmpty()) {
            return;
        }

        $resourceName = strtolower(class_basename($resources[0]));

        $events = [];

        $resources->each(function ($resource) use ($resources, $endpoint, &$events, $verb) {
            try {
                $eventId = $resource->$endpoint();
                $events[] = ["{$eventId}", $resource->name];
            } catch (\Exception $e) {
                if ($resources->count() === 1) {
                    $this->error("{$verb} failed on {$resource->name}.");
                }
            }
        });

        if (empty($events)) {
            $this->error(sprintf(ucfirst('%s failed on all %s.'), $verb, Str::plural($resourceName)));
            return;
        }

        $this->successfulStep(ucfirst(Str::plural($resourceName, count($events)) . " queued for $verb."));

        $this->stepTable([
            'Event ID',
            ucfirst($resourceName),
        ], $events);
    }
}
