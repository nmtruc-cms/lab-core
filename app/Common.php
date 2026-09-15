<?php

use CodeIgniter\Shield\Entities\User;

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

if (! function_exists('lab_core_current_user')) {
    function lab_core_current_user(): ?User
    {
        $auth = auth();

        return $auth->loggedIn() ? $auth->user() : null;
    }
}

if (! function_exists('lab_core_is_super_user')) {
    function lab_core_is_super_user(): bool
    {
        $user = lab_core_current_user();

        if ($user === null) {
            return false;
        }

        return $user->inGroup('super_user');
    }
}

if (! function_exists('lab_core_company_name')) {
    function lab_core_company_name(): string
    {
        try {
            $row = model(\Modules\Core\Models\CompanyProfileModel::class)
                ->orderBy('id', 'asc')
                ->first();
        } catch (Throwable) {
            return 'SQS Lab Core';
        }

        return $row['name'] ?? 'SQS Lab Core';
    }
}

if (! function_exists('lab_core_user_display_name')) {
    function lab_core_user_display_name(): string
    {
        $user = lab_core_current_user();

        if ($user === null) {
            return 'Guest User';
        }

        return $user->username ?? $user->email ?? 'User';
    }
}

if (! function_exists('lab_core_user_org')) {
    function lab_core_user_org(): string
    {
        $company = lab_core_company_name();
        $user = lab_core_current_user();

        if ($user === null) {
            return $company;
        }

        $groups = $user->getGroups() ?? [];
        $group = $groups[0] ?? 'user';

        return strtoupper(str_replace('_', ' ', $group));
    }
}

if (! function_exists('lab_core_user_initials')) {
    function lab_core_user_initials(): string
    {
        $name = lab_core_user_display_name();
        $parts = preg_split('/[\s._-]+/', trim($name)) ?: [];
        $initials = '';

        foreach (array_slice($parts, 0, 2) as $part) {
            if ($part !== '') {
                $initials .= strtoupper(substr($part, 0, 1));
            }
        }

        return $initials !== '' ? $initials : 'LC';
    }
}

if (! function_exists('lab_core_can')) {
    function lab_core_can(?string $permission): bool
    {
        if ($permission === null) {
            return true;
        }

        $user = lab_core_current_user();

        return $user !== null && $user->can($permission);
    }
}

if (! function_exists('lab_core_supported_locales')) {
    /**
     * @return array<string, string>
     */
    function lab_core_supported_locales(): array
    {
        return [
            'en' => 'English',
            'vi' => 'Tiếng Việt',
        ];
    }
}

if (! function_exists('lab_core_current_locale')) {
    function lab_core_current_locale(): string
    {
        $locale = service('request')->getLocale();
        $supported = array_keys(lab_core_supported_locales());

        return in_array($locale, $supported, true) ? $locale : config('App')->defaultLocale;
    }
}

if (! function_exists('lab_core_request_path')) {
    function lab_core_request_path(): string
    {
        return service('request')->getUri()->getPath();
    }
}

if (! function_exists('lab_core_path_is_active')) {
    function lab_core_path_is_active(string $path, string $match = 'prefix'): bool
    {
        $current = trim(lab_core_request_path(), '/');
        $path = trim($path, '/');

        if ($match === 'exact') {
            return $current === $path;
        }

        return $current === $path || ($path !== '' && str_starts_with($current, $path . '/'));
    }
}

if (! function_exists('lab_core_menu_is_active')) {
    function lab_core_menu_is_active(string $path, array $children = [], string $match = 'prefix'): bool
    {
        if (lab_core_path_is_active($path, $match)) {
            return true;
        }

        foreach ($children as $child) {
            $childPath = trim($child['path'] ?? '', '/');
            $childMatch = $child['match'] ?? 'prefix';

            if ($childPath !== '' && lab_core_path_is_active($childPath, $childMatch)) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('lab_core_admin_menu')) {
    function lab_core_admin_menu(): array
    {
        $items = [
            [
                'label' => lang('Core.admin.dashboard'),
                'path' => 'admin',
                'url' => site_url('admin'),
                'icon' => 'home',
                'permission' => 'core.access',
                'match' => 'exact',
            ],
            [
                'label' => lang('Core.admin.administration'),
                'path' => 'admin/users',
                'url' => site_url('admin/users'),
                'icon' => 'users',
                'permission' => 'core.users.view',
                'children' => [
                    [
                        'label' => lang('Core.admin.users'),
                        'path' => 'admin/users',
                        'url' => site_url('admin/users'),
                        'permission' => 'core.users.view',
                    ],
                    [
                        'label' => lang('Core.admin.roles'),
                        'path' => 'admin/roles',
                        'url' => site_url('admin/roles'),
                        'permission' => 'core.roles.view',
                    ],
                    [
                        'label' => lang('Core.admin.departments'),
                        'path' => 'admin/departments',
                        'url' => site_url('admin/departments'),
                        'permission' => 'core.departments.view',
                    ],
                    [
                        'label' => lang('Core.admin.companyProfile'),
                        'path' => 'admin/company-profile',
                        'url' => site_url('admin/company-profile'),
                        'permission' => 'core.company_profile.view',
                    ],
                ],
            ],
            [
                'label' => 'IMS',
                'path' => 'ims',
                'url' => site_url('ims'),
                'icon' => 'inventory',
                'permission' => 'ims.access',
                'match' => 'exact',
                'children' => [
                    [
                        'label' => lang('IMS.menu.dashboard'),
                        'path' => 'ims',
                        'url' => site_url('ims'),
                        'permission' => 'ims.dashboard.view',
                        'match' => 'exact',
                    ],
                    [
                        'label' => lang('IMS.menu.masterData'),
                        'path' => 'ims/master-data',
                        'url' => site_url('ims/master-data'),
                        'permission' => 'ims.items.view',
                    ],
                    [
                        'label' => lang('IMS.menu.items'),
                        'path' => 'ims/items',
                        'url' => site_url('ims/items'),
                        'permission' => 'ims.items.view',
                    ],
                    [
                        'label' => lang('IMS.menu.suppliers'),
                        'path' => 'ims/suppliers',
                        'url' => site_url('ims/suppliers'),
                        'permission' => 'ims.suppliers.view',
                    ],
                    [
                        'label' => lang('IMS.menu.stockLots'),
                        'path' => 'ims/lots',
                        'url' => site_url('ims/lots'),
                        'permission' => 'ims.stock.view',
                    ],
                    [
                        'label' => lang('IMS.menu.formulation'),
                        'path' => 'ims/formulations',
                        'url' => site_url('ims/formulations'),
                        'permission' => 'ims.stock.view',
                    ],
                    [
                        'label' => lang('IMS.menu.locations'),
                        'path' => 'ims/locations',
                        'url' => site_url('ims/locations'),
                        'permission' => 'ims.warehouses.view',
                    ],
                    [
                        'label' => lang('IMS.menu.transactions'),
                        'path' => 'ims/transactions',
                        'url' => site_url('ims/transactions'),
                        'permission' => 'ims.stock.view',
                    ],
                    [
                        'label' => lang('IMS.menu.requests'),
                        'path' => 'ims/requests',
                        'url' => site_url('ims/requests'),
                        'permission' => 'ims.items.view',
                    ],
                    [
                        'label' => lang('IMS.menu.purchaseOrders'),
                        'path' => 'ims/purchase-orders',
                        'url' => site_url('ims/purchase-orders'),
                        'permission' => 'ims.stock.view',
                    ],

                ],
            ],
        ];

        return array_values(array_filter($items, static function (array $item): bool {
            if (! lab_core_can($item['permission'] ?? null)) {
                return false;
            }

            $children = $item['children'] ?? [];

            if ($children !== []) {
                $allowedChildren = array_values(array_filter($children, static fn(array $child): bool => lab_core_can($child['permission'] ?? null)));

                if ($allowedChildren === []) {
                    return false;
                }
            }

            return true;
        }));
    }
}

if (! function_exists('lab_core_home_url')) {
    function lab_core_home_url(): string
    {
        return lab_core_is_super_user()
            ? site_url('admin')
            : site_url('dashboard');
    }
}
