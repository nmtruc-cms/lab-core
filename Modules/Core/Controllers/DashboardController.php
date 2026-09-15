<?php

declare(strict_types=1);

namespace Modules\Core\Controllers;

use CodeIgniter\Shield\Models\UserModel;
use Modules\Core\Models\CompanyProfileModel;
use Modules\Core\Models\DepartmentModel;
use Modules\Core\Models\UserDepartmentModel;

class DashboardController extends BaseAdminController
{
    public function index(): string
    {
        return $this->render('Modules\Core\Views\admin\dashboard', [
            'pageTitle' => 'Admin Dashboard',
            'pageSubtitle' => 'Core platform overview and quick-start administration.',
            'stats' => [
                [
                    'label' => 'Users',
                    'value' => model(UserModel::class)->countAllResults(),
                    'accent' => 'navy',
                ],
                [
                    'label' => 'Departments',
                    'value' => model(DepartmentModel::class)->countAllResults(),
                    'accent' => 'orange',
                ],
                [
                    'label' => 'Company Profiles',
                    'value' => model(CompanyProfileModel::class)->countAllResults(),
                    'accent' => 'blue',
                ],
                [
                    'label' => 'User-Department Links',
                    'value' => model(UserDepartmentModel::class)->countAllResults(),
                    'accent' => 'slate',
                ],
            ],
            'quickLinks' => [
                ['label' => 'Manage Users', 'url' => site_url('admin/users'), 'description' => 'Review implementation accounts and access groups.'],
                ['label' => 'Role Base', 'url' => site_url('admin/roles'), 'description' => 'Inspect Shield-backed dynamic roles and permission mappings.'],
                ['label' => 'Departments', 'url' => site_url('admin/departments'), 'description' => 'Create the structure that users and workflows will attach to.'],
                ['label' => 'Company Profile', 'url' => site_url('admin/company-profile'), 'description' => 'Maintain organization header, contact, and branding metadata.'],
            ],
        ]);
    }
}
