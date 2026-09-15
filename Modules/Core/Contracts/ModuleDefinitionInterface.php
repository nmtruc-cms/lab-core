<?php

declare(strict_types=1);

namespace Modules\Core\Contracts;

interface ModuleDefinitionInterface
{
    public function key(): string;

    public function name(): string;

    public function description(): string;

    public function version(): string;

    /**
     * @return list<string>
     */
    public function dependencies(): array;

    public function routeGroup(): string;

    public function permissionNamespace(): string;

    public function enabledByDefault(): bool;

    public function isCore(): bool;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
