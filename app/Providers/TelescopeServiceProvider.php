<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\IncomingExceptionEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    public function register(): void
    {
        // Telescope::night();

        $this->hideSensitiveRequestDetails();

        Telescope::filter(function (IncomingEntry $entry) {
            if (! $this->app->isProduction()) {
                return true;
            }

            return $entry->isReportableException() ||
                $entry->isFailedRequest() ||
                $entry->isFailedJob() ||
                $entry->isScheduledTask() ||
                $entry->hasMonitoredTag();
        });

        Telescope::afterStoring(function (array $entries, $batchId) {
            foreach ($entries as $entry) {
                if (! ($entry instanceof IncomingExceptionEntry)) {
                    continue;
                }

                if (app()->runningInConsole()) {
                    continue;
                }

                if (! $entry->isReportableException()) {
                    continue;
                }

                logger()->channel('slack')->critical($entry->exception->getMessage(), [
                    'environment' => app()->environment(),
                    'url' => app()->runningInConsole() ? 'CLI' : request()->fullUrl(),
                    'user' => $entry->content['user'] ?? '-',
                    'view in telescope' => url(config('telescope.path', 'telescope')."/exceptions/{$entry->uuid}"),
                    'hash' => $entry->familyHash(),
                ]);
            }
        });
    }

    protected function hideSensitiveRequestDetails(): void
    {
        if (! $this->app->isProduction()) {
            return;
        }

        Telescope::hideRequestParameters(['_token', 'token']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    protected function gate(): void
    {
        Gate::define('viewTelescope', function ($user) {
            return in_array($user->email, [
                //
            ]);
        });
    }
}
