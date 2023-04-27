<?php

namespace App;

use Closure;
use SpinupWp\Resources\Resource;

class Field
{
    protected string $name;

    protected string $displayName;

    protected array $aliases = [];

    protected ?Closure $ignoreRule = null;

    protected ?Closure $transformRule = null;

    protected bool $booleanField = false;

    protected bool $shouldCapitalize = false;

    protected bool $enabledOrDisabled = false;

    public function __construct(string $displayName, string $name)
    {
        $this->displayName = $displayName;
        $this->name        = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function getDisplayLabel(bool $isLongVersion = false): string
    {
        if ($isLongVersion) {
            return $this->displayName;
        }

        return $this->name;
    }

    public function withTransformRule(Closure $rule): self
    {
        $this->transformRule = $rule;

        return $this;
    }

    public function shouldTransform(): bool
    {
        return !(is_null($this->transformRule));
    }

    /**
     * @return mixed
     */
    public function transform(Resource $resource)
    {
        if (is_null($this->transformRule)) {
            return $resource->{$this->name};
        }

        return ($this->transformRule)($resource->{$this->name});
    }

    public function withIgnoreRule(Closure $ignoreRule): self
    {
        $this->ignoreRule = $ignoreRule;

        return $this;
    }

    public function shouldIgnore(Resource $resource): bool
    {
        if (!$this->ignoreRule) {
            return false;
        }

        return ($this->ignoreRule)($resource->{$this->name});
    }

    public function withAliases(array $aliases): self
    {
        $this->aliases = $aliases;
        return $this;
    }

    public function isInFilter(array $fieldsFilter): bool
    {
        $aliases = array_merge([$this->name], $this->aliases);
        return count(array_intersect($aliases, $fieldsFilter)) > 0;
    }

    public function yesOrNo(): self
    {
        $this->booleanField = true;
        return $this;
    }

    public function isBoolean(): bool
    {
        return $this->booleanField;
    }

    public function displayYesOrNo(Resource $resource): string
    {
        return $resource->{$this->name} ? 'Yes' : 'No';
    }

    public function withFirstCharUpperCase(): self
    {
        $this->shouldCapitalize = true;
        return $this;
    }

    public function shouldFirstCharMustBeUpperCase(): bool
    {
        return $this->shouldCapitalize;
    }

    public function displayFirstCharUpperCase(Resource $resource): string
    {
        return ucfirst($resource->{$this->name});
    }

    public function couldBeEnabledOrDisabled(): self
    {
        $this->enabledOrDisabled = true;
        return $this;
    }

    public function getEnabledOrDisabled(): bool
    {
        return $this->enabledOrDisabled;
    }

    public function displayEnabledOrDisabled(Resource $resource): string
    {
        return $resource->{$this->name}['enabled'] ? 'Enabled' : 'Disabled';
    }

    /**
     * @return mixed
     */
    public function getDisplayValue(Resource $resource)
    {
        if ($this->shouldIgnore($resource)) {
            return '';
        }

        if ($this->isBoolean()) {
            return $this->displayYesOrNo($resource);
        }

        if ($this->shouldFirstCharMustBeUpperCase()) {
            return $this->displayFirstCharUpperCase($resource);
        }

        if ($this->getEnabledOrDisabled()) {
            return $this->displayEnabledOrDisabled($resource);
        }

        if (!$this->shouldTransform()) {
            return $resource->{$this->getName()};
        }

        return $this->transform($resource);
    }
}
