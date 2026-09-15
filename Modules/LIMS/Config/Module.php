<?php

declare(strict_types=1);

namespace Modules\LIMS\Config;

use Modules\Core\Config\BaseModule;

class Module extends BaseModule
{
    protected string $key = 'lims';
    protected string $name = 'Laboratory Information Management System';
    protected string $description = 'Sample lifecycle, testing workflows, and laboratory result management.';
    protected string $version = '1.0.0';
    protected string $routeGroup = 'lims';
    protected string $permissionNamespace = 'lims';
    protected bool $enabledByDefault = false;

    /**
     * @var list<string>
     */
    protected array $dependencies = ['core'];
}
