<?php

namespace App\Commands\Concerns;

use DeliciousBrains\SpinupWp\Resources\Resource;

trait SpecifyFields
{
    protected array $fieldsMap = [];

    protected function specifyFields(Resource $resource, array $fieldsFilter = []): array
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
            $fieldsOption = str_replace(' ', '', strval($this->option('fields')));
            $fieldsFilter = explode(',', $fieldsOption);
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

    /**
     * @param mixed $property
     * @return string
     */
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

    protected function saveFieldsFilter(bool $saveConfiguration = false): void
    {
        $commandOptions = $this->config->getCommandConfiguration($this->command, $this->profile());

        if (!empty($commandOptions)) {
            return;
        }

        if (empty($commandOptions) && !$saveConfiguration) {
            $saveConfiguration = $this->confirm('Do you want to save the specified fields as the default for this command?', true);
        }

        if ($saveConfiguration) {
            $this->config->setCommandConfiguration($this->command, 'fields', strval($this->option('fields')), $this->profile());
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
