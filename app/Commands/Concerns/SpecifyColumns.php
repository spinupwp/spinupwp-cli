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
