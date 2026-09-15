<?php

declare(strict_types=1);

namespace Modules\QMS\Config;

use Modules\Core\Config\BaseModule;

class Module extends BaseModule
{
    protected string $key = 'qms';
    protected string $name = 'Quality Management System';
    protected string $description = 'Documents, CAPA, deviations, training, and quality workflows.';
    protected string $version = '1.0.0';
    protected string $routeGroup = 'qms';
    protected string $permissionNamespace = 'qms';
    protected bool $enabledByDefault = false;

    /**
     * @var list<string>
     */
    protected array $dependencies = ['core'];
}
