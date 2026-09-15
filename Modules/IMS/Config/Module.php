<?php

declare(strict_types=1);

namespace Modules\IMS\Config;

use Modules\Core\Config\BaseModule;

class Module extends BaseModule
{
    protected string $key = 'ims';
    protected string $name = 'Inventory Management System';
    protected string $description = 'Inventory, stock movements, purchasing support, and warehouse operations.';
    protected string $version = '1.0.0';
    protected string $routeGroup = 'ims';
    protected string $permissionNamespace = 'ims';
    protected bool $enabledByDefault = true;

    /**
     * @var list<string>
     */
    protected array $dependencies = ['core'];
}
