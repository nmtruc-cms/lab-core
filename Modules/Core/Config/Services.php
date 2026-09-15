<?php

declare(strict_types=1);

namespace Modules\Core\Config;

use CodeIgniter\Config\BaseService;
use Modules\Core\Services\ModuleRegistry;
use Modules\Core\Services\RoleManager;

class Services extends BaseService
{
    public static function moduleRegistry(bool $getShared = true): ModuleRegistry
    {
        if ($getShared) {
            return static::getSharedInstance('moduleRegistry');
        }

        return new ModuleRegistry(config(Registry::class));
    }

    public static function roleManager(bool $getShared = true): RoleManager
    {
        if ($getShared) {
            return static::getSharedInstance('roleManager');
        }

        return new RoleManager(
            config(PermissionCatalog::class),
            config(RoleTemplates::class),
        );
    }
}
