<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->group('', [
    'namespace' => 'Modules\Core\Controllers',
], static function (RouteCollection $routes): void {
    $routes->get('language/(:segment)', 'LanguageController::switch/$1');
});

$routes->group('admin', [
    'namespace' => 'Modules\Core\Controllers',
    'filter' => 'session',
], static function (RouteCollection $routes): void {
    $routes->get('/', 'DashboardController::index', ['filter' => 'permission:core.access']);
    $routes->get('users', 'UserManagementController::index', ['filter' => 'permission:core.users.view']);
    $routes->post('users', 'UserManagementController::create', ['filter' => 'permission:core.users.create']);
    $routes->post('users/(:num)', 'UserManagementController::update/$1', ['filter' => 'permission:core.users.update']);
    $routes->post('users/(:num)/magic-link', 'UserManagementController::generateMagicLink/$1', ['filter' => 'permission:core.users.update']);
    $routes->get('roles', 'RoleManagementController::index', ['filter' => 'permission:core.roles.view']);
    $routes->get('departments', 'DepartmentController::index', ['filter' => 'permission:core.departments.view']);
    $routes->post('departments', 'DepartmentController::store', ['filter' => 'permission:core.departments.manage']);
    $routes->post('departments/(:num)', 'DepartmentController::update/$1', ['filter' => 'permission:core.departments.manage']);
    $routes->post('departments/(:num)/delete', 'DepartmentController::delete/$1', ['filter' => 'permission:core.departments.manage']);
    $routes->get('company-profile', 'CompanyProfileController::index', ['filter' => 'permission:core.company_profile.view']);
    $routes->post('company-profile', 'CompanyProfileController::save', ['filter' => 'permission:core.company_profile.update']);
});

$routes->group('', [
    'namespace' => 'Modules\Core\Controllers',
    'filter' => 'session',
], static function (RouteCollection $routes): void {
    $routes->get('dashboard', 'LandingController::index');
    $routes->get('set-password', 'SetPasswordController::show');
    $routes->post('set-password', 'SetPasswordController::save');
});
