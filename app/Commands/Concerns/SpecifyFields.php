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

        collect($this->fieldsMap)->each(function (Field $field) use ($resource, &$fields) {
            $label = $field->getDisplayLabel($this->displayFormat() === 'table');
            if (!property_exists($resource, $field->getName())) {
                return;
            }

            if ($field->shouldIgnore($resource)) {
                $fields[$label] = '';
                return;
            }

            if (!$field->shouldTransform()) {
                $fields[$label] = $resource->{$field->getName()};
                return;
            }

            if ($field->isBoolean()) {
                $fields[$label] = $field->displayYesOrNo($resource);
                return;
            }

            if ($field->shouldFirstCharMustBeUpperCase()) {
                $fields[$label] = $field->displayFirstCharUpperCase($resource);
                return;
            }

            if ($field->getEnabledOrDisabled()) {
                $fields[$label] = $field->displayEnabledOrDisabled($resource);
                return;
            }

            $value = $field->transform($resource);

            if (!is_array($value)) {
                $fields[$label] = $value;
                return;
            }

            foreach ($value as $key => $_value) {
                $fields[$key] = $_value;
            }
        });
        return $fields;
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
            $this->fieldsMap = array_filter($this->fieldsMap, fn (Field $field) => $field->isInFilter($fieldsFilter));
        }
    }

    protected function shouldSpecifyFields(): bool
    {
        $commandOptions = $this->config->getCommandConfiguration($this->command, $this->profile());
        return $this->option('fields') || (isset($commandOptions['fields']) && !empty($commandOptions['fields']));
    }
}
