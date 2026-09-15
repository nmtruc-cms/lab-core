<?php

declare(strict_types=1);

namespace Modules\Core\Config;

class Module extends BaseModule
{
    protected string $key = 'core';
    protected string $name = 'Core Platform';
    protected string $description = 'Shared authentication, authorization, settings, navigation, and platform services.';
    protected string $version = '1.0.0';
    protected string $routeGroup = 'admin';
    protected string $permissionNamespace = 'core';
    protected bool $enabledByDefault = true;
    protected bool $core = true;
}
