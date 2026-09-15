<?php

declare(strict_types=1);

namespace Modules\Core\Controllers;

class RoleManagementController extends BaseAdminController
{
    public function index(): string
    {
        return $this->render('Modules\Core\Views\admin\roles', [
            'pageTitle' => 'Role Base',
            'pageSubtitle' => 'Dynamic roles and permissions stored in Shield settings.',
            'groups' => setting('AuthGroups.groups') ?? [],
            'matrix' => setting('AuthGroups.matrix') ?? [],
            'permissions' => setting('AuthGroups.permissions') ?? [],
            'defaultGroup' => setting('AuthGroups.defaultGroup'),
        ]);
    }
}
