<?php

declare(strict_types=1);

namespace Modules\Core\Services;

use Config\Database;
use Modules\Core\Config\Registry;
use Modules\Core\Contracts\ModuleDefinitionInterface;
use Modules\Core\Models\ModuleRegistryModel;
use Throwable;

class ModuleRegistry
{
    /**
     * @var array<string, ModuleDefinitionInterface>
     */
    private array $definitions = [];

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $runtimeOverrides = [];

    public function __construct(private readonly Registry $registryConfig)
    {
        $this->definitions = $this->loadDefinitions();
        $this->runtimeOverrides = $this->loadRuntimeOverrides();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $modules = [];

        foreach ($this->definitions as $definition) {
            $modules[] = $this->normalize($definition);
        }

        return $modules;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function enabled(): array
    {
        return array_values(array_filter(
            $this->all(),
            static fn (array $module): bool => $module['enabled'] === true
        ));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $key): ?array
    {
        $definition = $this->definitions[$key] ?? null;

        if ($definition === null) {
            return null;
        }

        return $this->normalize($definition);
    }

    public function isEnabled(string $key): bool
    {
        $module = $this->find($key);

        return $module !== null && $module['enabled'] === true;
    }

    /**
     * @return array<string, ModuleDefinitionInterface>
     */
    private function loadDefinitions(): array
    {
        $definitions = [];

        foreach ($this->registryConfig->modules as $moduleClass) {
            $module = new $moduleClass();

            $definitions[$module->key()] = $module;
        }

        return $definitions;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function loadRuntimeOverrides(): array
    {
        try {
            $db = Database::connect();

            if (! $db->tableExists('core_modules')) {
                return [];
            }

            $rows = model(ModuleRegistryModel::class)
                ->findAll();

            $overrides = [];

            foreach ($rows as $row) {
                $overrides[$row['code']] = $row;
            }

            return $overrides;
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function normalize(ModuleDefinitionInterface $definition): array
    {
        $manifest = $definition->toArray();
        $runtime = $this->runtimeOverrides[$definition->key()] ?? [];

        $manifest['enabled'] = array_key_exists('enabled', $runtime)
            ? (bool) $runtime['enabled']
            : $definition->enabledByDefault();
        $manifest['installed_version'] = $runtime['version'] ?? $definition->version();
        $manifest['installed_at'] = $runtime['installed_at'] ?? null;
        $manifest['updated_at'] = $runtime['updated_at'] ?? null;

        return $manifest;
    }
}
