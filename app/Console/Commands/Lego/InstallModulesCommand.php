<?php

namespace App\Console\Commands\Lego;

use App\Console\Commands\Lego\Traits\CanListModules;
use App\Support\Folder;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Symfony\Component\Process\Process;

use function Laravel\Prompts\multiselect;

class InstallModulesCommand extends Command
{
    use CanListModules;

    protected $signature =
        'lego:install {module-names?* : One or more or the module names specified in the lego:list-modules command}
        {--r|register : Whether to register the ModuleServiceProvider in config/concord.php automatically }
        {--dev : Keep the .git folder (development mode)}';

    protected $description = 'Install lego modules';

    private bool $anyModuleInstalled = false;

    private array $documentationMessages = [];

    public function handle(): int
    {
        $this->readModulesManifestFile();
        if ($this->hasNoModules()) {
            return 0;
        }

        $moduleNames = $this->argument('module-names');

        if (empty($moduleNames)) {
            $moduleNames = $this->selectModulesInteractively();

            if (empty($moduleNames)) {
                $this->warn('No modules selected. Exiting...');

                return 0;
            }
        }

        $this->installModules($moduleNames);

        if (count($this->documentationMessages) > 0) {
            $this->info("\nRunning composer update");
            $update = new Process(['composer', 'update']);
            $update->run();
            echo $update->getOutput();
            $this->newLine();

            foreach ($this->documentationMessages as $documentationMessage) {
                $this->info($documentationMessage);
            }

            $this->newLine();

            (new Process(['php', 'artisan', 'optimize:clear']))->run();
            (new Process(['composer', 'dump-autoload']))->run();
        }

        return 0;
    }

    private function installModules(array $moduleNames): void
    {
        collect($moduleNames)->each(
            fn ($moduleName) => $this->installModule($moduleName)
        );
    }

    private function selectModulesInteractively(): array
    {
        $availableModules = collect($this->modules)->map(function ($module) {
            $name = data_get($module, 'name');
            $description = data_get($module, 'description');
            $isInstalled = file_exists(base_path('modules/'.$name));

            return [
                'name' => $name,
                'description' => $description,
                'installed' => $isInstalled,
                'label' => $isInstalled
                    ? "{$name} - {$description} [INSTALLED]"
                    : "{$name} - {$description}",
            ];
        })->sortBy('name')->values();

        $options = $availableModules->pluck('label', 'name')->toArray();

        $selected = multiselect(
            label: 'Select modules to install (use Space to select, Enter to confirm):',
            options: $options,
            required: false,
            hint: 'You can select multiple modules. Already installed modules are marked.'
        );

        return $selected;
    }

    private function installModule(string $moduleName): void
    {
        if (! preg_match('/^[a-zA-Z0-9_-]+$/', $moduleName)) {
            $this->error("Invalid module name: {$moduleName}");

            return;
        }

        $module = $this->findModule($moduleName);
        if (! $module) {
            $this->warn("Module $moduleName not found, skipping ...");

            return;
        }

        $dependencies = $this->getModuleDependencies($moduleName);
        if (count($dependencies) > 0) {
            $this->info("Installing dependencies for {$moduleName} ...");
            $this->installModules($dependencies);
        }

        $this->createModulesFolderWhenNeeded();

        $installPath = base_path('modules/'.$moduleName);
        $repoPath = data_get($module, 'repo');
        $branch = data_get($module, 'branch', 'main');
        $docPath = data_get($module, 'doc');

        if (file_exists($installPath)) {
            $this->warn("Module $moduleName is already installed, skipping ...");
        } else {
            $this->info("\nInstalling $moduleName ...");

            $this->cloneRepository($moduleName, $repoPath, $branch, $installPath);

            $gitFolder = $installPath.'/.git';

            if (! $this->option('dev') && is_dir($gitFolder)) {
                $this->info("Removing module git folder: $gitFolder ...");
                Folder::deleteRecursively($gitFolder);
            } elseif ($this->option('dev')) {
                $this->info('Dev mode enabled — keeping module git folder.');
            }

            if ($this->option('register')) {
                $this->info("Registering $moduleName service provider in config/concord.php ...");
                $this->registerProviderToConcordFile($installPath);
            }

            $this->documentationMessages[] =
                "Please refer to {$moduleName}'s documentation at {$docPath} for post install requirements.";
        }
    }

    private function cloneRepository(string $moduleName, string $repoPath, string $branch, string $installPath): void
    {
        $clone = new Process(['git', 'clone', $repoPath, $installPath]);
        $clone->run();

        if (! $clone->isSuccessful()) {
            $this->error("Failed to clone {$moduleName} from {$repoPath}.");

            if (is_dir($installPath)) {
                $this->info("Removing incomplete installation at {$installPath} ...");
                Folder::deleteRecursively($installPath);
            }

            exit(1);
        }

        $verify = new Process(['git', 'ls-remote', '--heads', 'origin', $branch], $installPath);
        $verify->run();

        if (! $verify->isSuccessful()) {
            $this->error("Failed to verify branch {$branch} for {$moduleName}.");
            $this->info("Removing incomplete installation at {$installPath} ...");
            Folder::deleteRecursively($installPath);

            exit(1);
        }

        $checkout = new Process(['git', 'checkout', $branch], $installPath);
        $checkout->run();

        if (! $checkout->isSuccessful()) {
            $this->error("Failed to checkout branch {$branch} for {$moduleName}.");

            if (is_dir($installPath)) {
                $this->info("Removing incomplete installation at {$installPath} ...");
                Folder::deleteRecursively($installPath);
            }

            exit(1);
        }

        $this->info("Successfully cloned {$moduleName} from {$repoPath} on branch {$branch}.");
    }

    private function createModulesFolderWhenNeeded(): void
    {
        if (! file_exists(base_path('modules'))) {
            mkdir(base_path('modules'));
        }
    }

    /**
     * Adds the module's ModuleServiceProvider to the config/concord.php file.
     *
     * @param  string  $installPath  The absolute path to the installed module.
     */
    private function registerProviderToConcordFile(string $installPath): bool
    {
        $path = config_path('concord.php');

        if (! file_exists($path) || ! is_writable($path)) {
            $this->error('Unable to write to config/concord.php. Please check file permissions.');

            return false;
        }

        $provider = $this->getFqcnClassStringFromFilePath($installPath.'/src/Providers/ModuleServiceProvider.php');

        if (! $provider) {
            $this->warn("Could not determine the service provider for the module at {$installPath}. Skipping registration.");

            return false;
        }

        // 1. Read the existing array definition to merge and sort the providers.
        $config = require $path;

        // 2. Generate the full, formatted string of providers for the config file.
        $providers = (new Collection($config['modules']))
            ->merge([$provider])
            ->unique()
            ->sort()
            ->values()
            ->map(fn ($p) => '        \\'.$p.'::class,')
            ->implode(PHP_EOL);

        // 3. Read the entire config file content as a string.
        $configContent = file_get_contents($path);

        // 4. Define the regex pattern for in-place replacement.
        $pattern = "/('modules' =>\s*\[)(.*?)(],)/s";

        // 5. Build the replacement string using the captured groups ($1, $3) and the new content.
        $replacement = "$1\n".$providers."\n    $3";

        // 6. Perform the replacement (limit to 1 occurrence)
        $newContent = preg_replace($pattern, $replacement, $configContent, 1);

        // 7. Write the modified content back to the file.
        if ($newContent === null || $newContent === $configContent) {
            $this->warn('Failed to update config/concord.php automatically.');
        } else {
            file_put_contents($path, $newContent);
        }

        return true;
    }

    /**
     * Get the Fully Qualified Class Name (FQCN) from a PHP file path.
     *
     * This method reads a PHP file, extracts its namespace and class name
     * to construct the FQCN.
     *
     * @param  string  $path  The absolute path to the PHP file.
     * @return string|null The FQCN of the class, or null if the file cannot be read.
     */
    private function getFqcnClassStringFromFilePath(string $path): ?string
    {
        $contents = file_get_contents($path);
        if ($contents === false) {
            return null;
        }

        $tokens = token_get_all($contents);

        $namespace = '';
        $className = '';

        $lookingForNamespace = false;
        $lookingForClass = false;

        foreach ($tokens as $token) {
            if (! is_array($token)) {
                // Handle simple tokens (like ';')
                if ($lookingForNamespace && ($token === ';' || $token === '{')) {
                    $lookingForNamespace = false;
                }

                continue;
            }

            $type = $token[0];

            if ($type === T_NAMESPACE) {
                $lookingForNamespace = true;

                continue;
            }

            if ($lookingForNamespace) {
                // These tokens build up the namespace
                if ($type === T_STRING || $type === T_NS_SEPARATOR || (defined('T_NAME_QUALIFIED') && $type === T_NAME_QUALIFIED)) {
                    $namespace .= $token[1];
                }
            }

            // Look for class, interface, trait, or enum
            if ($type === T_CLASS || $type === T_INTERFACE || $type === T_TRAIT || $type === T_ENUM) {
                $lookingForClass = true;

                continue;
            }

            // The T_STRING right after a T_CLASS is the class name
            if ($lookingForClass && $type === T_STRING) {
                $className = $token[1];
                break; // Found what we need
            }
        }

        if (empty($className)) {
            return null; // No class found in this file
        }

        // T_NAME_QUALIFIED (PHP 8+) already includes the backslashes
        if ($namespace && ! str_ends_with($namespace, '\\')) {
            $namespace .= '\\';
        }

        return $namespace.$className;
    }
}
