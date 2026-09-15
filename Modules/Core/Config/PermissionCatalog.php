<?php

declare(strict_types=1);

namespace Modules\Core\Config;

use CodeIgniter\Config\BaseConfig;

class PermissionCatalog extends BaseConfig
{
    /**
     * Atomic permissions used by Shield authorization checks.
     *
     * @var array<string, string>
     */
    public array $permissions = [
        'core.access'                 => 'Access the back-office platform.',
        'core.company_profile.view'   => 'View company profile information.',
        'core.company_profile.update' => 'Update company profile information.',
        'core.departments.view'       => 'View departments.',
        'core.departments.manage'     => 'Create, update, and delete departments.',
        'core.users.view'             => 'View users.',
        'core.users.create'           => 'Create users.',
        'core.users.update'           => 'Update users.',
        'core.users.delete'           => 'Delete users.',
        'core.roles.view'             => 'View role base configuration.',
        'core.roles.manage'           => 'Create and update role base configuration.',
        'core.settings.manage'        => 'Manage system settings.',

        'ims.access'                  => 'Access IMS module.',
        'ims.dashboard.view'          => 'View IMS dashboard.',
        'ims.items.view'              => 'View inventory items.',
        'ims.items.create'            => 'Create inventory items.',
        'ims.items.update'            => 'Update inventory items.',
        'ims.items.delete'            => 'Delete inventory items.',
        'ims.requests.view'           => 'View item requests.',
        'ims.requests.create'         => 'Create item requests.',
        'ims.requests.update'         => 'Update item requests.',
        'ims.requests.delete'         => 'Delete item requests.',
        'ims.requests.submit'         => 'Submit item requests for approval.',
        'ims.requests.approve'        => 'Approve assigned item requests.',
        'ims.requests.reject'         => 'Reject assigned item requests.',
        'ims.warehouses.view'         => 'View warehouses.',
        'ims.warehouses.manage'       => 'Create and update warehouses.',
        'ims.suppliers.view'          => 'View suppliers.',
        'ims.suppliers.manage'        => 'Create and update suppliers.',
        'ims.stock.view'              => 'View stock balances and transaction history.',
        'ims.stock.receive'           => 'Receive stock into warehouse.',
        'ims.stock.issue'             => 'Issue stock out of warehouse.',
        'ims.stock.adjust'            => 'Adjust stock balances.',
        'ims.reports.view'            => 'View IMS reports.',
    ];
}
