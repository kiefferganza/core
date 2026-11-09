<?php

namespace App\Console\Commands\Lego\Traits;

use Exception;

trait CanListModules
{
    private $modulesManifest = null;

    private $modules = null;

    private function readModulesManifestFile(): void
    {
        try {
            $this->modulesManifest = json_decode(
                file_get_contents(base_path('lego-modules.json')),
                true
            );
        } catch (Exception $e) {
            $this->error("Can't find lego modules json file.");

            return;
        }
    }

    private function hasNoModules(): bool
    {
        $moduleCount = count($this->getModules());
        if ($moduleCount > 0) {
            return false;
        }
        $this->info('No modules to list.');

        return true;
    }

    private function getModules(): array
    {
        if (! $this->modules) {
            $this->modules = $this->modulesManifest['modules'] ?? [];
        }

        return $this->modules;
    }

    private function getModuleDependencies($moduleName)
    {
        $module = collect($this->getModules())
            ->firstWhere('name', $moduleName);

        return $module['dependencies'] ?? [];
    }

    private function findModule($moduleName)
    {
        return collect($this->getModules())
            ->firstWhere('name', $moduleName);
    }
}
