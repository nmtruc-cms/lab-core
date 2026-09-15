<?php

declare(strict_types=1);

namespace Modules\Core\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;

class AuthDemoUsersSeeder extends Seeder
{
    private const DEFAULT_PASSWORD = '123456';

    /**
     * @var array<string, array{email: string, group: string, username: string}>
     */
    private array $accounts = [
        'super_user' => [
            'email' => 'super_user@lab-core.com',
            'group' => 'super_user',
            'username' => 'super_user',
        ],
        'lab_manager' => [
            'email' => 'lab_manager@lab-core.com',
            'group' => 'lab_manager',
            'username' => 'lab_manager',
        ],
        'director' => [
            'email' => 'director@lab-core.com',
            'group' => 'director',
            'username' => 'director',
        ],
        'qa' => [
            'email' => 'qa@lab-core.com',
            'group' => 'qa',
            'username' => 'qa',
        ],
        'sales' => [
            'email' => 'sales@lab-core.com',
            'group' => 'sales',
            'username' => 'sales',
        ],
        'service' => [
            'email' => 'service@lab-core.com',
            'group' => 'service',
            'username' => 'service',
        ],
        'lab_technician' => [
            'email' => 'lab_technician@lab-core.com',
            'group' => 'lab_technician',
            'username' => 'lab_technician',
        ],
        'lab_assistant' => [
            'email' => 'lab_assistant@lab-core.com',
            'group' => 'lab_assistant',
            'username' => 'lab_assistant',
        ],
    ];

    public function run(): void
    {
        service('roleManager')->bootstrapDefaults(true);

        /** @var UserModel $users */
        $users = model(UserModel::class);

        foreach ($this->accounts as $account) {
            $user = $users->findByCredentials(['email' => $account['email']]);

            if (! $user instanceof User) {
                $user = new User([
                    'username' => $account['username'],
                    'active' => true,
                ]);
            } else {
                $user->username = $account['username'];
                $user->active = true;
            }

            $user->email = $account['email'];
            $user->password = self::DEFAULT_PASSWORD;

            $users->save($user);

            if ($user->id === null) {
                $user = $users->findByCredentials(['email' => $account['email']]);
            }

            if (! $user instanceof User) {
                continue;
            }

            $user->syncGroups($account['group']);
            $user->syncPermissions();
        }
    }
}
