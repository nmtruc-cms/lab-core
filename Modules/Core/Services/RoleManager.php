<?php

declare(strict_types=1);

namespace Modules\Core\Services;

use Modules\Core\Config\PermissionCatalog;
use Modules\Core\Config\RoleTemplates;
use RuntimeException;

class RoleManager
{
    public function __construct(
        private readonly PermissionCatalog $permissionCatalog,
        private readonly RoleTemplates $roleTemplates,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function permissions(): array
    {
        return $this->permissionCatalog->permissions;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function roleTemplates(): array
    {
        return $this->roleTemplates->roles;
    }

    public function defaultGroup(): string
    {
        return $this->roleTemplates->defaultGroup;
    }

    public function bootstrapDefaults(bool $replaceExisting = true): void
    {
        $groups = [];
        $matrix = [];

        foreach ($this->roleTemplates() as $alias => $role) {
            $groups[$alias] = [
                'title' => $role['title'],
                'description' => $role['description'],
            ];

            $matrix[$alias] = $this->normalizePermissions($role['permissions'] ?? []);
        }

        $this->persist(
            $groups,
            $this->permissions(),
            $matrix,
            $this->defaultGroup(),
            $replaceExisting,
        );
    }

    /**
     * @param array<string, array{title: string, description: string}> $groups
     * @param array<string, string> $permissions
     * @param array<string, list<string>> $matrix
     */
    public function persist(
        array $groups,
        array $permissions,
        array $matrix,
        string $defaultGroup,
        bool $replaceExisting = false,
    ): void {
        if (! isset($groups[$defaultGroup])) {
            throw new RuntimeException("Default group '{$defaultGroup}' must exist in the role list.");
        }

        $currentGroups = setting('AuthGroups.groups') ?? [];
        $currentPermissions = setting('AuthGroups.permissions') ?? [];
        $currentMatrix = setting('AuthGroups.matrix') ?? [];

        $finalGroups = $replaceExisting ? $groups : array_replace($currentGroups, $groups);
        $finalPermissions = $replaceExisting ? $permissions : array_replace($currentPermissions, $permissions);
        $finalMatrix = $replaceExisting ? $matrix : array_replace($currentMatrix, $matrix);

        setting('AuthGroups.groups', $finalGroups);
        setting('AuthGroups.permissions', $finalPermissions);
        setting('AuthGroups.matrix', $finalMatrix);
        setting('AuthGroups.defaultGroup', $defaultGroup);
    }

    /**
     * @param list<string> $permissions
     * @return list<string>
     */
    public function normalizePermissions(array $permissions): array
    {
        $knownPermissions = array_keys($this->permissions());
        $normalized = [];

        foreach ($permissions as $permission) {
            $permission = strtolower(trim($permission));

            if ($permission === '') {
                continue;
            }

            if (str_ends_with($permission, '.*')) {
                $scope = substr($permission, 0, -2) . '.';
                $matches = [];

                foreach ($knownPermissions as $knownPermission) {
                    if (str_starts_with($knownPermission, $scope)) {
                        $matches[] = $knownPermission;
                    }
                }

                if ($matches === []) {
                    throw new RuntimeException("Unknown permission scope '{$permission}' in role definition.");
                }

                array_push($normalized, ...$matches);

                continue;
            }

            if (! in_array($permission, $knownPermissions, true)) {
                throw new RuntimeException("Unknown permission '{$permission}' in role definition.");
            }

            $normalized[] = $permission;
        }

        return array_values(array_unique($normalized));
    }
}
