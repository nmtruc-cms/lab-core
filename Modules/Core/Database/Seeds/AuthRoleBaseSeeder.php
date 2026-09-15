<?php

declare(strict_types=1);

namespace Modules\Core\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AuthRoleBaseSeeder extends Seeder
{
    public function run(): void
    {
        service('roleManager')->bootstrapDefaults(true);
    }
}
