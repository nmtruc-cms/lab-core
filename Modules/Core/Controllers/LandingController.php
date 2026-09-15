<?php

declare(strict_types=1);

namespace Modules\Core\Controllers;

class LandingController extends BaseAdminController
{
    public function index(): string
    {
        $registry = service('moduleRegistry');
        $isSuperUser = lab_core_is_super_user();

        $cards = [
            [
                'code' => 'ims',
                'title' => 'IMS',
                'subtitle' => 'Inventory Management System',
                'description' => 'Inventory, stock movement, receiving, issuing, and supplier-facing workflows.',
                'highlights' => [
                    'Warehouse and material control',
                    'Stock receiving and issuing',
                    'Ready to expand into reports and approvals',
                ],
                'meta' => [
                    ['label' => 'Module', 'value' => $registry->isEnabled('ims') ? 'Enabled' : 'Disabled'],
                    ['label' => 'Access', 'value' => lab_core_can('ims.access') ? 'Granted' : 'Restricted'],
                ],
                'url' => $registry->isEnabled('ims') && lab_core_can('ims.access') ? site_url('ims') : null,
                'status' => $registry->isEnabled('ims') ? 'Available' : 'Not enabled',
                'statusTone' => $registry->isEnabled('ims') ? 'success' : 'muted',
                'accent' => 'orange',
                'icon' => 'fa-boxes-stacked',
                'cta' => 'Open Workspace',
                'note' => $registry->isEnabled('ims')
                    ? 'Current active operational module.'
                    : 'Enable the IMS module to open this workspace.',
            ],
            [
                'code' => 'lims',
                'title' => 'LIMS',
                'subtitle' => 'Laboratory Information Management System',
                'description' => 'Sample lifecycle, testing execution, and result handling for laboratory operations.',
                'highlights' => [
                    'Sample registration and tracking',
                    'Testing workflow and result capture',
                    'Built to connect with the shared Core platform',
                ],
                'meta' => [
                    ['label' => 'Module', 'value' => $registry->isEnabled('lims') ? 'Enabled' : 'Planned'],
                    ['label' => 'Readiness', 'value' => 'Blueprint ready'],
                ],
                'url' => null,
                'status' => 'Planned',
                'statusTone' => 'muted',
                'accent' => 'navy',
                'icon' => 'fa-flask-vial',
                'cta' => 'Coming Soon',
                'note' => 'Module shell is reserved for the next implementation phase.',
            ],
            [
                'code' => 'qms',
                'title' => 'QMS',
                'subtitle' => 'Quality Management System',
                'description' => 'Deviation, CAPA, document control, and compliance workflows for quality teams.',
                'highlights' => [
                    'Deviation and CAPA lifecycle',
                    'Document and compliance control',
                    'Shared permissions and audit-ready foundation',
                ],
                'meta' => [
                    ['label' => 'Module', 'value' => $registry->isEnabled('qms') ? 'Enabled' : 'Planned'],
                    ['label' => 'Readiness', 'value' => 'Blueprint ready'],
                ],
                'url' => null,
                'status' => 'Planned',
                'statusTone' => 'muted',
                'accent' => 'blue',
                'icon' => 'fa-shield-halved',
                'cta' => 'Coming Soon',
                'note' => 'Reserved for quality workflows and controlled process expansion.',
            ],
        ];

        if ($isSuperUser) {
            $cards[] = [
                'code' => 'admin',
                'title' => 'Quản trị',
                'subtitle' => 'Administration Workspace',
                'description' => 'Manage users, roles, departments, company profile, and platform-wide configuration.',
                'highlights' => [
                    'User, role, and department governance',
                    'Core settings and company profile',
                    'Reserved for super administrators only',
                ],
                'meta' => [
                    ['label' => 'Access', 'value' => 'Super admin only'],
                    ['label' => 'Scope', 'value' => 'Core platform'],
                ],
                'url' => site_url('admin'),
                'status' => 'Privileged',
                'statusTone' => 'warning',
                'accent' => 'dark',
                'icon' => 'fa-user-shield',
                'cta' => 'Open Admin',
                'note' => 'This workspace is hidden from all standard lab roles.',
            ];
        }

        return view('Modules\Core\Views\dashboard\landing', [
            'pageTitle' => 'Module Dashboard',
            'pageSubtitle' => 'Choose the workspace you want to enter and track the current module rollout status.',
            'pageIntro' => 'Core authentication and permissions are shared across all modules. Each card reflects current rollout readiness and your access level.',
            'cards' => $cards,
            'summaryCards' => [
                [
                    'label' => 'Available Now',
                    'value' => count(array_filter($cards, static fn(array $card): bool => $card['url'] !== null)),
                    'tone' => 'orange',
                ],
                [
                    'label' => 'Planned Modules',
                    'value' => count(array_filter($cards, static fn(array $card): bool => $card['status'] === 'Planned')),
                    'tone' => 'navy',
                ],
                [
                    'label' => 'Your Access Level',
                    'value' => $isSuperUser ? 'Super Admin' : 'Standard',
                    'tone' => 'blue',
                ],
            ],
        ]);
    }
}
