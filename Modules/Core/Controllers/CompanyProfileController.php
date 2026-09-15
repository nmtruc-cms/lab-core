<?php

declare(strict_types=1);

namespace Modules\Core\Controllers;

use Modules\Core\Models\CompanyProfileModel;

class CompanyProfileController extends BaseAdminController
{
    public function index(): string
    {
        return $this->render('Modules\Core\Views\admin\company_profile', [
            'pageTitle' => 'Company Profile',
            'pageSubtitle' => 'Organization branding, contacts, tax, and address details.',
            'profile' => model(CompanyProfileModel::class)->orderBy('id', 'asc')->first() ?? [],
            'validation' => session('errors') ?? [],
            'message' => session('message'),
        ]);
    }

    public function save()
    {
        $rules = [
            'name' => 'required|min_length[2]|max_length[255]',
            'email' => 'permit_empty|valid_email|max_length[255]',
            'web' => 'permit_empty|max_length[255]',
            'phone' => 'permit_empty|max_length[50]',
            'vat_code' => 'permit_empty|max_length[50]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $payload = [
            'name_header' => trim((string) $this->request->getPost('name_header')),
            'name' => trim((string) $this->request->getPost('name')),
            'address' => trim((string) $this->request->getPost('address')),
            'vat_code' => trim((string) $this->request->getPost('vat_code')),
            'email' => trim((string) $this->request->getPost('email')),
            'logo' => trim((string) $this->request->getPost('logo')),
            'phone' => trim((string) $this->request->getPost('phone')),
            'web' => trim((string) $this->request->getPost('web')),
        ];

        $model = model(CompanyProfileModel::class);
        $current = $model->orderBy('id', 'asc')->first();

        if ($current === null) {
            $model->insert($payload);
        } else {
            $model->update($current['id'], $payload);
        }

        return redirect()->to(site_url('admin/company-profile'))->with('message', 'Company profile saved successfully.');
    }
}
