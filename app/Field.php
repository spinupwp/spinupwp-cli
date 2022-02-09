<?php

namespace App;

use Closure;
use DeliciousBrains\SpinupWp\Resources\Resource;

class Field
{
    protected string $name;

    protected string $displayName;

    protected array $aliases = [];

    protected ?Closure $ignoreRule = null;

    protected ?Closure $transformRule = null;

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

    public function transform(Resource $resource)
    {
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

        return $this->ignoreRule($resource->{$this->name});
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
}
