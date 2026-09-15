<?php

declare(strict_types=1);

namespace Modules\Core\Config;

use CodeIgniter\Config\BaseConfig;

class Registry extends BaseConfig
{
    /**
     * Canonical list of modules supported by this codebase.
     *
     * @var list<class-string<\Modules\Core\Contracts\ModuleDefinitionInterface>>
     */
    public array $modules = [
        \Modules\Core\Config\Module::class,
        \Modules\IMS\Config\Module::class,
        \Modules\LIMS\Config\Module::class,
        \Modules\QMS\Config\Module::class,
    ];
}
