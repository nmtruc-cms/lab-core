<?php

declare(strict_types=1);

namespace Modules\IMS\Controllers;

use Modules\Core\Controllers\BaseAdminController;

abstract class BaseImsController extends BaseAdminController
{
    /**
     * @param array<string, mixed> $data
     */
    protected function render(string $view, array $data = []): string
    {
        $defaults = [
            'pageTitle' => 'IMS Workspace',
            'pageSubtitle' => 'Inventory Management System',
        ];

        return parent::render($view, array_merge($defaults, $data));
    }
}
