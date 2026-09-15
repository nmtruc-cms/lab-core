<?php

declare(strict_types=1);

namespace Modules\Core\Config;

use CodeIgniter\Config\BaseConfig;

class RoleTemplates extends BaseConfig
{
    public string $defaultGroup = 'lab_assistant';

    /**
     * Default role templates for a lab deployment.
     *
     * @var array<string, array<string, mixed>>
     */
    public array $roles = [
        'super_user' => [
            'title' => 'Super User',
            'description' => 'Platform-wide super user for implementation, setup, and administration.',
            'permissions' => [
                'core.*',
                'ims.*',
            ],
        ],
        'director' => [
            'title' => 'Director',
            'description' => 'Executive oversight across laboratory operations and reporting.',
            'permissions' => [
                'core.access',
                'core.company_profile.view',
                'core.departments.view',
                'core.users.view',
                'core.roles.view',
                'ims.access',
                'ims.dashboard.view',
                'ims.items.view',
                'ims.requests.view',
                'ims.warehouses.view',
                'ims.suppliers.view',
                'ims.stock.view',
                'ims.reports.view',
            ],
        ],
        'lab_manager' => [
            'title' => 'Lab Manager',
            'description' => 'Operational owner of the lab and inventory workflows.',
            'permissions' => [
                'core.access',
                'core.company_profile.view',
                'core.company_profile.update',
                'core.departments.view',
                'core.departments.manage',
                'core.users.view',
                'core.users.create',
                'core.users.update',
                'core.roles.view',
                'ims.access',
                'ims.dashboard.view',
                'ims.items.view',
                'ims.items.create',
                'ims.items.update',
                'ims.items.delete',
                'ims.requests.view',
                'ims.requests.create',
                'ims.requests.update',
                'ims.requests.delete',
                'ims.requests.submit',
                'ims.requests.approve',
                'ims.requests.reject',
                'ims.warehouses.view',
                'ims.warehouses.manage',
                'ims.suppliers.view',
                'ims.suppliers.manage',
                'ims.stock.view',
                'ims.stock.receive',
                'ims.stock.issue',
                'ims.stock.adjust',
                'ims.reports.view',
            ],
        ],
        'qa' => [
            'title' => 'QA',
            'description' => 'Quality assurance role with oversight and verification access.',
            'permissions' => [
                'core.access',
                'core.company_profile.view',
                'core.departments.view',
                'ims.access',
                'ims.dashboard.view',
                'ims.items.view',
                'ims.requests.view',
                'ims.warehouses.view',
                'ims.suppliers.view',
                'ims.stock.view',
                'ims.reports.view',
            ],
        ],
        'sales' => [
            'title' => 'Sales',
            'description' => 'Commercial role that needs visibility into stock and availability.',
            'permissions' => [
                'core.access',
                'ims.access',
                'ims.dashboard.view',
                'ims.items.view',
                'ims.requests.view',
                'ims.stock.view',
                'ims.reports.view',
            ],
        ],
        'service' => [
            'title' => 'Service',
            'description' => 'Service and support role with operational stock visibility.',
            'permissions' => [
                'core.access',
                'ims.access',
                'ims.dashboard.view',
                'ims.items.view',
                'ims.requests.view',
                'ims.warehouses.view',
                'ims.stock.view',
                'ims.stock.issue',
            ],
        ],
        'lab_technician' => [
            'title' => 'Lab Technician',
            'description' => 'Primary operator performing stock receipt and issue transactions.',
            'permissions' => [
                'core.access',
                'ims.access',
                'ims.dashboard.view',
                'ims.items.view',
                'ims.requests.view',
                'ims.warehouses.view',
                'ims.suppliers.view',
                'ims.stock.view',
                'ims.stock.receive',
                'ims.stock.issue',
            ],
        ],
        'lab_assistant' => [
            'title' => 'Lab Assistant',
            'description' => 'Supporting role with limited operational visibility.',
            'permissions' => [
                'core.access',
                'ims.access',
                'ims.dashboard.view',
                'ims.items.view',
                'ims.requests.view',
                'ims.stock.view',
            ],
        ],
    ];
}
