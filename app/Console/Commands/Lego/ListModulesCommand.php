<?php

namespace App\Console\Commands\Lego;

use App\Console\Commands\Lego\Traits\CanListModules;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class ListModulesCommand extends Command
{
    use CanListModules;

    protected $signature = 'lego:list-modules';

    protected $description = 'Lists lego modules';

    public function handle()
    {
        $this->readModulesManifestFile();
        if ($this->hasNoModules()) {
            return;
        }
        $this->showCatalog();
    }

    private function showCatalog()
    {
        $modules = Arr::map(
            $this->getModules(),
            fn ($module) => Arr::only($module, ['name', 'description'])
        );

        $this->info("\nAvailable Lego Modules:");
        $this->table(['Module Name', 'Description'], $modules);
        $this->line('');
    }
}
