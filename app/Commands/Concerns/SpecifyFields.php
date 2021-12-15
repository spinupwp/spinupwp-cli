<?php

namespace App\Commands\Concerns;

trait SpecifyFields
{
    protected array $fieldsMap = [];

    protected function specifyFields($resource, array $fieldsFilter = []): array
    {
        if (empty($this->fieldsMap)) {
            return $resource->toArray();
        }

        $fields = [];

        $commandFields = $this->config->getCommandConfiguration($this->command, $this->profile())['fields'] ?? null;

        if ($commandFields) {
            $fieldsFilter = explode(',', str_replace(' ', '', $commandFields));
        }

        if ($this->option('fields')) {
            $fieldsFilter = explode(',', str_replace(' ', '', $this->option('fields')));
        }

        $this->applyFilter($fieldsFilter);

        foreach ($this->fieldsMap as $name => $resourceProp) {
            $property = $this->getFinalResourceProperty($resourceProp);

            if (isset($resourceProp['ignore']) && $resourceProp['ignore']($resource->{$property})) {
                $fields[$name] = '';
                continue;
            }

            if (isset($resourceProp['filter'])) {
                $value = $resourceProp['filter']($resource->{$property});

                if (is_array($value)) {
                    foreach ($value as $key => $_value) {
                        $fields[$key] = $_value;
                    }
                    continue;
                }

                $fields[$name] = $value;
                continue;
            }

            $fields[$name] = $resource->{$resourceProp};
        }

        return $fields;
    }

    protected function propertyInFilter(string $property, array $fieldsFilter): bool
    {
        if (strpos($property, '|') !== false) {
            $properties = explode('|', $property);
            return in_array($properties[0], $fieldsFilter) || in_array($properties[1], $fieldsFilter);
        }
        return in_array($property, $fieldsFilter);
    }

    protected function getFinalResourceProperty($property): string
    {
        if (is_array($property)) {
            $property = $property['property'];
        }

        if (strpos($property, '|') !== false) {
            return explode('|', $property)[0];
        }

        return $property;
    }

    protected function saveFieldsFilter($saveConfiguration = false): void
    {
        $commandOptions = $this->config->getCommandConfiguration($this->command, $this->profile());

        if (!empty($commandOptions)) {
            return;
        }

        if (empty($commandOptions) && !$saveConfiguration) {
            $saveConfiguration = $this->confirm('Do you want to save the specified fields as default for this command?', true);
        }

        if ($saveConfiguration) {
            $this->config->setCommandConfiguration($this->command, 'fields', $this->option('fields'), $this->profile());
            return;
        }

        $this->config->setCommandConfiguration($this->command, 'fields', '', $this->profile());
    }

    protected function applyFilter(array $fieldsFilter): void
    {
        if (!empty($fieldsFilter)) {
            $this->fieldsMap = array_filter($this->fieldsMap, function ($field) use ($fieldsFilter) {
                if (is_array($field)) {
                    $field = $field['property'];
                }
                return $this->propertyInFilter($field, $fieldsFilter);
            });
        }
    }
}
