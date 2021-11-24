<?php

namespace App\Commands\Concerns;

use Symfony\Component\Console\Helper\Table;

trait HasLargeOutput
{
    protected bool $largeOutput = false;

    protected array $columnsMaxWidths = [];

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
}
