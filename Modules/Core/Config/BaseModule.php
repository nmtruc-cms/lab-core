<?php

declare(strict_types=1);

namespace Modules\Core\Config;

use Modules\Core\Contracts\ModuleDefinitionInterface;

abstract class BaseModule implements ModuleDefinitionInterface
{
    protected string $key = '';
    protected string $name = '';
    protected string $description = '';
    protected string $version = '0.1.0';
    protected string $routeGroup = '';
    protected string $permissionNamespace = '';
    protected bool $enabledByDefault = false;
    protected bool $core = false;

    /**
     * @var list<string>
     */
    protected array $dependencies = [];

    public function key(): string
    {
        return $this->key;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function version(): string
    {
        return $this->version;
    }

    public function dependencies(): array
    {
        return $this->dependencies;
    }

    public function routeGroup(): string
    {
        return $this->routeGroup;
    }

    public function permissionNamespace(): string
    {
        return $this->permissionNamespace;
    }

    public function enabledByDefault(): bool
    {
        return $this->enabledByDefault;
    }

    public function isCore(): bool
    {
        return $this->core;
    }

    public function toArray(): array
    {
        return [
            'key'                  => $this->key(),
            'name'                 => $this->name(),
            'description'          => $this->description(),
            'version'              => $this->version(),
            'dependencies'         => $this->dependencies(),
            'route_group'          => $this->routeGroup(),
            'permission_namespace' => $this->permissionNamespace(),
            'enabled_by_default'   => $this->enabledByDefault(),
            'is_core'              => $this->isCore(),
        ];
    }
}
