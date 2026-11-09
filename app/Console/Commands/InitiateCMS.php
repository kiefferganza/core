<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class InitiateCMS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'initiate-cms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("\nInstalling Admin Module");

        Artisan::call('lego:install AdminModule');

        $this->info("\nRegistering Admin Module");

        $this->registerAdminModule();

        $this->info("\nInstalling Admin Panel");

        Artisan::call('filament:install --panels --no-interaction');

        $this->updateCmsAuthGuard();

        $this->info("\nCheck AdminModule documentation how to create admin user (https://gitlab.com/appetiser/baseplate/backend/admin-module)");

        $this->info("\nLogin to ".config('app.url').'/admin/login');
    }

    private function updateCmsAuthGuard(): void
    {
        $appBootstrapPath = app_path('Providers/Filament/AdminPanelProvider.php');

        $content = file_get_contents($appBootstrapPath);

        $append = "->authGuard('cms_admin')\n            ";

        if (! Str::contains($content, $append)) {
            $pos = strpos($content, '->authMiddleware');

            $modifiedContent = substr_replace($content, $append, $pos, 0);

            (new Filesystem())->put($appBootstrapPath, $modifiedContent, true);
        }
    }

    private function registerAdminModule(): void
    {
        $concordPath = config_path('concord.php');

        $content = file_get_contents($concordPath);

        $append = "    \Appetiser\Admin\Providers\ModuleServiceProvider::class\n    ";

        if (! Str::contains($content, $append)) {
            $pos = strpos($content, '],');

            $modifiedContent = substr_replace($content, $append, $pos, 0);

            (new Filesystem())->put($concordPath, $modifiedContent, true);
        }
    }
}
