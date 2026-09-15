<?php

declare(strict_types=1);

namespace Modules\Core\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Exceptions\PageNotFoundException;

class ModuleEnabledFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $module = $arguments[0] ?? null;

        if ($module === null) {
            return null;
        }

        if (! service('moduleRegistry')->isEnabled((string) $module)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
