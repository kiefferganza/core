<?php

namespace App\Console\Commands\Lego;

use App\Console\Commands\Lego\Traits\CanListModules;
use App\Support\Folder;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class CreateModulesCommand extends Command
{
    use CanListModules;

    protected $signature =
        'lego:create {module-name : Name of module}';

    protected $description = 'Create new lego module';

    public function handle(): int
    {
        $moduleName = $this->argument('module-name');
        $this->installModule($moduleName);

        $this->newLine();

        $this->info("Please refer to baseplate's documentation at https://gitlab.com/appetiser/baseplate/backend/module-boilerplate/-/blob/main/readme.md for post install requirements.");

        $this->newLine();

        (new Process(['php', 'artisan', 'optimize:clear']))->run();
        (new Process(['composer', 'dump-autoload']))->run();

        return 0;
    }

    private function installModule(string $moduleName): void
    {
        $repositoryUrl = 'git@gitlab.com:appetiser/baseplate/backend/module-boilerplate.git';
        $this->createModulesFolderWhenNeeded();

        $installPath = base_path('modules/'.$moduleName);
        if (file_exists($installPath)) {
            $this->warn("Module $moduleName is already installed, skipping ...");
        } else {
            $this->info("\nInstalling $moduleName ...");
            $clone = new Process(['git', 'clone', $repositoryUrl, $installPath]);
            $clone->run();

            if (! $clone->isSuccessful()) {
                $this->error('Failed to clone module boilerplate.');
                exit(1);
            }

            $gitFolder = $installPath.'/.git';
            $this->info("Removing module git folder: $gitFolder ...");
            Folder::deleteRecursively($gitFolder);

            $this->info("Updating namespaces for module: $moduleName ...");
            $this->updateNamespaces($moduleName, $installPath);
        }
    }

    private function createModulesFolderWhenNeeded(): void
    {
        if (! file_exists(base_path('modules'))) {
            mkdir(base_path('modules'));
        }
    }

    private function updateNamespaces(string $moduleName, string $installPath): void
    {
        $oldNamespace = 'Appetiser\\ModuleBoilerplate';
        $newNamespace = "Appetiser\\{$moduleName}";

        // Update PHP files
        $this->updatePhpFiles($installPath, $oldNamespace, $newNamespace);

        // Update composer.json
        $this->updateComposerJson($installPath, $oldNamespace, $newNamespace, $moduleName);
    }

    private function updatePhpFiles(string $installPath, string $oldNamespace, string $newNamespace): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($installPath)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());

                // Replace namespace declarations
                $content = str_replace(
                    "namespace {$oldNamespace}",
                    "namespace {$newNamespace}",
                    $content
                );

                // Replace use statements
                $content = str_replace(
                    "use {$oldNamespace}\\",
                    "use {$newNamespace}\\",
                    $content
                );

                // Replace fully qualified class names
                $content = str_replace(
                    $oldNamespace.'\\',
                    $newNamespace.'\\',
                    $content
                );

                file_put_contents($file->getPathname(), $content);
            }
        }
    }

    private function updateComposerJson(string $installPath, string $oldNamespace, string $newNamespace, string $moduleName): void
    {
        $composerFile = $installPath.'/composer.json';

        if (file_exists($composerFile)) {
            $composer = json_decode(file_get_contents($composerFile), true);

            // Update package name
            $composer['name'] = "appetiser/module-{$moduleName}";

            // Update description
            $composer['description'] = "Module {$moduleName}";

            // Update autoload PSR-4 mappings
            if (isset($composer['autoload']['psr-4'])) {
                $newAutoload = [];
                foreach ($composer['autoload']['psr-4'] as $namespace => $path) {
                    $updatedNamespace = str_replace($oldNamespace, $newNamespace, $namespace);
                    $newAutoload[$updatedNamespace] = $path;
                }
                $composer['autoload']['psr-4'] = $newAutoload;
            }

            // Update composer scripts if they reference the old namespace
            if (isset($composer['scripts']['post-create-project-cmd'])) {
                $composer['scripts']['post-create-project-cmd'] = array_map(
                    function ($script) use ($oldNamespace, $newNamespace) {
                        return str_replace($oldNamespace, $newNamespace, $script);
                    },
                    $composer['scripts']['post-create-project-cmd']
                );
            }

            file_put_contents(
                $composerFile,
                json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );
        }
    }
}
