<?php

namespace App\Commands\Concerns;

trait SpecifyColumns
{
    protected array $columnsMap = [];

    protected function specifyColumns($resource): array
    {
        if (empty($this->columnsMap)) {
            return $resource->toArray();
        }

        $columns = [];

        if ($this->option('columns')) {
            $columnsFilter    = explode(',', str_replace(' ', '', $this->option('columns')));
            $this->columnsMap = array_filter($this->columnsMap, function ($column) use ($columnsFilter) {
                if (!is_array($column)) {
                    return in_array($column, $columnsFilter);
                }
                return in_array($column['property'], $columnsFilter);
            });
        }

        foreach ($this->columnsMap as $name => $resourceProp) {
            if (isset($resourceProp['filter'])) {
                $columns[$name] = $resourceProp['filter']($resource->{$resourceProp['property']});
                continue;
            }
            $columns[$name] = $resource->{$resourceProp};
        }

        return $columns;
    }
}
