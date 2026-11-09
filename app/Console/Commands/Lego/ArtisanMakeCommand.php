<?php

namespace App\Console\Commands\Lego;

use App\Console\Commands\Lego\Traits\ArtisanMakeCommandHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class ArtisanMakeCommand extends Command
{
    use ArtisanMakeCommandHelper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lego:make';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Implements the Laravel \'php artisan make:[make file] [-options] command\'';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $module = $this->showCatalog();
        $makeChoice = $this->makeNamespaceChoice();
        $fileName = $this->getFileName();
        $flags = $this->getFlags();
        $args = array_merge(
            ['php', 'artisan', "make:{$makeChoice}", "{$module}/{$fileName}"],
            $flags ? array_values(array_filter(array_map('trim', explode(' ', trim($flags))))) : []
        );
        $process = Process::run(array_values($args));

        $process->failed() ? $this->error($process->output()) : $this->info($process->output());

        Process::run(['php', 'artisan', 'optimize:clear']);

        return 0;
    }
}
