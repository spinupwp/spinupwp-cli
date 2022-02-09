<?php

namespace App\Commands\Concerns;

use App\Field;
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

        collect($this->fieldsMap)->each(function (Field $field) use ($resource) {
            if (!property_exists($resource, $field->getName())) {
                return;
            }

            if ($field->shouldIgnore($resource)) {
                $fields[$field->getDisplayLabel($this->displayFormat() === 'table')] = '';
            }
        });

        // foreach ($this->fieldsMap as $field) {
        // $property = $this->getFinalResourceProperty($resourceProp);

        // if (!property_exists($resource, $property)) {
        //     continue;
        // }

        // if (isset($resourceProp['ignore']) && $resourceProp['ignore']($resource->{$property})) {
        //     $fields[$this->displayFormat() === 'table' ? $name : $resourceProp['property']] = '';
        //     continue;
        // }

        // if (isset($resourceProp['filter'])) {
        //     $value = $resourceProp['filter']($resource->{$property});

        //     if (is_array($value)) {
        //         foreach ($value as $key => $_value) {
        //             $fields[$key] = $_value;
        //         }
        //         continue;
        //     }

        //     $fields[$this->displayFormat() === 'table' ? $name : $resourceProp['property']] = $value;
        //     continue;
        // }
        // $fields[$this->displayFormat() === 'table' ? $name : $resourceProp] = $resource->{$resourceProp};
        // }

        return $fields;
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
            // $this->fieldsMap = array_filter($this->fieldsMap, function ($field) use ($fieldsFilter) {
            //     if (is_array($field)) {
            //         $field = $field['property'];
            //     }
            //     return $this->propertyInFilter($field, $fieldsFilter);
            // });
            $this->fieldsMap = array_filter($this->fieldsMap, fn (Field $field) => $field->isInFilter($fieldsFilter));
        }
    }

    protected function shouldSpecifyFields(): bool
    {
        $commandOptions = $this->config->getCommandConfiguration($this->command, $this->profile());
        return $this->option('fields') || (isset($commandOptions['fields']) && !empty($commandOptions['fields']));
    }
}
