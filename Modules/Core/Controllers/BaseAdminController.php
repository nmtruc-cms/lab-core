<?php

declare(strict_types=1);

namespace Modules\Core\Controllers;

use App\Controllers\BaseController;

abstract class BaseAdminController extends BaseController
{
    /**
     * @param array<string, mixed> $data
     */
    protected function render(string $view, array $data = []): string
    {
        $defaults = [
            'pageTitle' => 'Lab Core',
            'pageSubtitle' => 'Administration',
        ];

        return view($view, array_merge($defaults, $data));
    }
}
