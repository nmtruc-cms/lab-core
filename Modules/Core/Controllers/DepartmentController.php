<?php

declare(strict_types=1);

namespace Modules\Core\Controllers;

use Config\Database;
use Modules\Core\Models\DepartmentModel;

class DepartmentController extends BaseAdminController
{
    public function index(): string
    {
        $db       = Database::connect();
        $userRows = $db->table('core_user_departments cud')
            ->select('cud.department_id, cud.is_primary, u.id AS user_id, u.username, ai.secret AS email, agu.group')
            ->join('users u', 'u.id = cud.user_id', 'left')
            ->join('auth_identities ai', 'ai.user_id = u.id AND ai.type = \'email_password\'', 'left')
            ->join('auth_groups_users agu', 'agu.user_id = u.id', 'left')
            ->orderBy('cud.is_primary', 'desc')
            ->orderBy('u.username', 'asc')
            ->get()->getResultArray();

        $deptUsersMap = [];
        foreach ($userRows as $row) {
            $deptUsersMap[(int) $row['department_id']][] = [
                'user_id'    => $row['user_id'],
                'username'   => $row['username'] ?? '',
                'email'      => $row['email'] ?? '',
                'group'      => $row['group'] ?? '',
                'is_primary' => (bool) $row['is_primary'],
            ];
        }

        return $this->render('Modules\Core\Views\admin\departments', [
            'pageTitle'    => 'Departments',
            'pageSubtitle' => 'Organizational structure shared across modules.',
            'departments'  => model(DepartmentModel::class)->orderBy('name', 'asc')->findAll(),
            'deptUsersMap' => $deptUsersMap,
            'validation'   => session('errors') ?? [],
            'modalState'   => session('dept_modal') ?? [],
        ]);
    }

    public function store()
    {
        $rules = [
            'name' => 'required|min_length[2]|max_length[150]|is_unique[departments.name]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('dept_modal', ['open' => 'add']);
        }

        model(DepartmentModel::class)->insert([
            'name' => trim((string) $this->request->getPost('name')),
        ]);

        return redirect()->to(site_url('admin/departments'))->with('message', 'Department created successfully.');
    }

    public function update(int $id)
    {
        $dept = model(DepartmentModel::class)->find($id);

        if ($dept === null) {
            return redirect()->to(site_url('admin/departments'))->with('message', 'Department not found.')->with('message_type', 'danger');
        }

        $rules = [
            'name' => "required|min_length[2]|max_length[150]|is_unique[departments.name,id,{$id}]",
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('dept_modal', ['open' => 'edit', 'id' => $id]);
        }

        model(DepartmentModel::class)->update($id, [
            'name' => trim((string) $this->request->getPost('name')),
        ]);

        return redirect()->to(site_url('admin/departments'))->with('message', 'Department updated successfully.');
    }

    public function delete(int $id)
    {
        $dept = model(DepartmentModel::class)->find($id);

        if ($dept === null) {
            return redirect()->to(site_url('admin/departments'))->with('message', 'Department not found.')->with('message_type', 'danger');
        }

        model(DepartmentModel::class)->delete($id);

        return redirect()->to(site_url('admin/departments'))->with('message', 'Department deleted.');
    }
}
