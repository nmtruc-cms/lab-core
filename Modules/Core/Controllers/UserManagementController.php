<?php

declare(strict_types=1);

namespace Modules\Core\Controllers;

use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Models\UserModel;
use Config\Database;
use Modules\Core\Models\DepartmentModel;

class UserManagementController extends BaseAdminController
{
    public function index(): string
    {
        $db      = Database::connect();
        $users   = model(UserModel::class)->withGroups()->findAll();

        $departmentRows = $db->table('core_user_departments cud')
            ->select('cud.user_id, cud.department_id, d.name, cud.is_primary')
            ->join('departments d', 'd.id = cud.department_id', 'left')
            ->orderBy('cud.user_id', 'asc')
            ->orderBy('cud.is_primary', 'desc')
            ->get()->getResultArray();

        $departmentMap = [];
        foreach ($departmentRows as $row) {
            $departmentMap[(int) $row['user_id']][] = [
                'name'          => $row['name'],
                'department_id' => (int) $row['department_id'],
                'is_primary'    => (bool) $row['is_primary'],
            ];
        }

        $nameRows = $db->table('users')
            ->select('id, first_name, last_name')
            ->whereNotIn('id', [0])
            ->get()->getResultArray();

        $nameMap = [];
        foreach ($nameRows as $row) {
            $nameMap[(int) $row['id']] = [
                'first_name' => $row['first_name'] ?? '',
                'last_name'  => $row['last_name'] ?? '',
            ];
        }

        $magicRows = $db->table('auth_identities')
            ->select('user_id, secret, expires')
            ->where('type', Session::ID_TYPE_MAGIC_LINK)
            ->where('expires >', Time::now()->toDateTimeString())
            ->get()->getResultArray();

        $magicLinkMap = [];
        foreach ($magicRows as $ml) {
            $magicLinkMap[(int) $ml['user_id']] = site_url('login/verify-magic-link') . '?token=' . $ml['secret'];
        }

        $permissionRows = $db->table('auth_permissions_users')
            ->select('user_id, permission')
            ->orderBy('permission', 'asc')
            ->get()
            ->getResultArray();

        $permissionMap = [];
        foreach ($permissionRows as $row) {
            $permissionMap[(int) $row['user_id']][] = (string) $row['permission'];
        }

        return $this->render('Modules\Core\Views\admin\users', [
            'pageTitle'    => 'User Management',
            'pageSubtitle' => 'Manage user accounts and access.',
            'users'        => $users,
            'departmentMap'=> $departmentMap,
            'nameMap'      => $nameMap,
            'magicLinkMap' => $magicLinkMap,
            'groups'       => setting('AuthGroups.groups') ?? [],
            'permissions'  => setting('AuthGroups.permissions') ?? [],
            'permissionMap'=> $permissionMap,
            'departments'  => model(DepartmentModel::class)->orderBy('name', 'asc')->findAll(),
            'validation'   => session('errors') ?? [],
            'modalState'   => session('user_modal') ?? [],
        ]);
    }

    public function create()
    {
        $post = $this->request->getPost();

        $rules = [
            'first_name' => 'required|min_length[2]|max_length[100]',
            'last_name'  => 'required|min_length[2]|max_length[100]',
            'email'      => 'required|valid_email',
            'role'       => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('user_modal', ['open' => 'add']);
        }

        $firstName = trim((string) ($post['first_name'] ?? ''));
        $lastName  = trim((string) ($post['last_name'] ?? ''));
        $email     = trim((string) ($post['email'] ?? ''));
        $role      = (string) ($post['role'] ?? '');
        $deptId    = (int) ($post['department_id'] ?? 0) ?: null;

        $db = Database::connect();

        $emailTaken = $db->table('auth_identities')
            ->where('type', 'email_password')
            ->where('secret', $email)
            ->countAllResults() > 0;

        if ($emailTaken) {
            return redirect()->back()->withInput()
                ->with('errors', ['email' => 'This email is already registered.'])
                ->with('user_modal', ['open' => 'add']);
        }

        $username   = $this->generateUsername($firstName, $lastName);
        $userModel  = model(UserModel::class);
        $userEntity = new User(['username' => $username, 'active' => 0]);
        $userEntity->setEmail($email);
        $userEntity->setPassword(bin2hex(random_bytes(16)));

        if (! $userModel->save($userEntity)) {
            return redirect()->back()->withInput()
                ->with('errors', $userModel->errors())
                ->with('user_modal', ['open' => 'add']);
        }

        $userId = (int) $userModel->getInsertID();

        $db->table('users')->where('id', $userId)->update([
            'first_name' => $firstName,
            'last_name'  => $lastName,
        ]);

        $db->table('auth_groups_users')->insert([
            'user_id'    => $userId,
            'group'      => $role,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if ($deptId !== null) {
            $db->table('core_user_departments')->insert([
                'user_id'       => $userId,
                'department_id' => $deptId,
                'is_primary'    => 1,
            ]);
        }

        $this->syncDirectPermissions($userId, $post['permissions'] ?? []);

        $magicLinkUrl = $this->createMagicLink($userId);

        return redirect()->to(site_url('admin/users'))
            ->with('message', "User {$username} created successfully.")
            ->with('magic_link_url', $magicLinkUrl)
            ->with('magic_link_for', "{$firstName} {$lastName}");
    }

    public function update(int $id)
    {
        $db         = Database::connect();
        $userModel  = model(UserModel::class);
        $userEntity = $userModel->findById($id);

        if ($userEntity === null) {
            return redirect()->to(site_url('admin/users'))
                ->with('message', 'User not found.')
                ->with('message_type', 'danger');
        }

        $post = $this->request->getPost();

        $rules = [
            'first_name' => 'required|min_length[2]|max_length[100]',
            'last_name'  => 'required|min_length[2]|max_length[100]',
            'role'       => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('user_modal', ['open' => 'edit', 'id' => $id]);
        }

        $firstName = trim((string) ($post['first_name'] ?? ''));
        $lastName  = trim((string) ($post['last_name'] ?? ''));
        $role      = (string) ($post['role'] ?? '');
        $deptId    = (int) ($post['department_id'] ?? 0) ?: null;
        $active    = isset($post['active']) ? 1 : 0;

        $userEntity->active = $active;
        $userModel->save($userEntity);

        $db->table('users')->where('id', $id)->update([
            'first_name' => $firstName,
            'last_name'  => $lastName,
        ]);

        $db->table('auth_groups_users')->where('user_id', $id)->delete();
        if ($role !== '') {
            $db->table('auth_groups_users')->insert([
                'user_id'    => $id,
                'group'      => $role,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $db->table('core_user_departments')->where('user_id', $id)->delete();
        if ($deptId !== null) {
            $db->table('core_user_departments')->insert([
                'user_id'       => $id,
                'department_id' => $deptId,
                'is_primary'    => 1,
            ]);
        }

        $this->syncDirectPermissions($id, $post['permissions'] ?? []);

        return redirect()->to(site_url('admin/users'))
            ->with('message', 'User updated successfully.');
    }

    public function generateMagicLink(int $id)
    {
        $user = model(UserModel::class)->findById($id);

        if ($user === null) {
            return redirect()->to(site_url('admin/users'))
                ->with('message', 'User not found.')
                ->with('message_type', 'danger');
        }

        $nameRow   = Database::connect()->table('users')->select('first_name, last_name')->where('id', $id)->get()->getRowArray();
        $firstName = $nameRow['first_name'] ?? '';
        $lastName  = $nameRow['last_name'] ?? '';
        $label     = trim("{$firstName} {$lastName}") ?: ($user->username ?? "User #{$id}");

        $url = $this->createMagicLink($id);

        return redirect()->to(site_url('admin/users'))
            ->with('magic_link_url', $url)
            ->with('magic_link_for', $label);
    }

    private function createMagicLink(int $userId): string
    {
        $userEntity    = model(UserModel::class)->findById($userId);
        $identityModel = model(UserIdentityModel::class);
        $token         = bin2hex(random_bytes(20));

        $identityModel->deleteIdentitiesByType($userEntity, Session::ID_TYPE_MAGIC_LINK);
        $identityModel->insert([
            'user_id' => $userId,
            'type'    => Session::ID_TYPE_MAGIC_LINK,
            'secret'  => $token,
            'expires' => Time::now()->addHours(72)->toDateTimeString(),
        ]);

        return site_url('login/verify-magic-link') . '?token=' . $token;
    }

    private function generateUsername(string $firstName, string $lastName): string
    {
        $base = strtolower(
            preg_replace('/[^a-z0-9.]/i', '',
                str_replace([' ', '_', '-'], '.', $firstName . '.' . $lastName)
            ) ?? ''
        );
        $base = trim($base, '.');

        $db       = Database::connect();
        $username = $base;
        $i        = 1;

        while ($db->table('users')->where('username', $username)->countAllResults() > 0) {
            $username = $base . $i;
            $i++;
        }

        return $username;
    }

    /**
     * @param mixed $permissions
     */
    private function syncDirectPermissions(int $userId, mixed $permissions): void
    {
        $selected = is_array($permissions) ? $permissions : [];
        $knownPermissions = array_keys(setting('AuthGroups.permissions') ?? []);
        $selected = array_values(array_unique(array_filter(
            array_map(static fn ($permission): string => trim((string) $permission), $selected),
            static fn (string $permission): bool => in_array($permission, $knownPermissions, true)
        )));

        $db = Database::connect();
        $db->table('auth_permissions_users')->where('user_id', $userId)->delete();

        if ($selected === []) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $rows = array_map(static fn (string $permission): array => [
            'user_id' => $userId,
            'permission' => $permission,
            'created_at' => $now,
        ], $selected);

        $db->table('auth_permissions_users')->insertBatch($rows);
    }
}
