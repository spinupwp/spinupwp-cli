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

        if (!empty($fieldsFilter)) {
            $this->fieldsMap = array_filter($this->fieldsMap, function ($field) use ($fieldsFilter) {
                if (!is_array($field)) {
                    return in_array($field, $fieldsFilter);
                }
                return in_array($field['property'], $fieldsFilter);
            });
        }

        foreach ($this->fieldsMap as $name => $resourceProp) {
            if (isset($resourceProp['ignore']) && $resourceProp['ignore']($resource->{$resourceProp['property']})) {
                continue;
            }

            if (isset($resourceProp['filter'])) {
                $value = $resourceProp['filter']($resource->{$resourceProp['property']});

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

    protected function saveFieldsFilter(): void
    {
        $commandOptions    = $this->config->getCommandConfiguration($this->command, $this->profile());
        $saveConfiguration = false;

        if (empty($commandOptions)) {
            $saveConfiguration = $this->confirm('Do you want to save the specified fields as default for this command', true);
        }

        if ($saveConfiguration) {
            $this->config->setCommandConfiguration($this->command, 'fields', $this->option('fields'), $this->profile());
            return;
        }

        $this->config->setCommandConfiguration($this->command, 'fields', '', $this->profile());
    }
}
