<?php

namespace App\Providers;

use Filament\Actions\Action;
use Filament\Panel;
use Filament\View\PanelsRenderHook;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Konekt\Concord\BaseBoxServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->preconfigureFilament();
    }

    public function boot(): void
    {
        if (! app()->isLocal()) {
            URL::forceScheme('https');
        }

        $this->mergeStrMacro();
        $this->mergeArrMacro();
        $this->mergeRequestMacro();

        $this->registerModuleLanguageFiles();
        $this->configureRateLimitting();
    }

    private function configureRateLimitting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(config('app.global_rate_limit'))->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('registration', function (Request $request) {
            if (app()->isLocal()) {
                return;
            }

            // allow only 20 registration request per IP Address
            return Limit::perMinute(config('app.registration_rate_limit'))->by($request->ip());
        });
    }

    private function preconfigureFilament(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            $panel->renderHook(
                PanelsRenderHook::CONTENT_END,
                fn () => Blade::render(
                    '<livewire:logout-dialog />'
                )
            )->renderHook(
                PanelsRenderHook::SIDEBAR_NAV_END,
                fn () => Blade::render('
                    <ul class="fi-sidebar-nav-groups -mx-2 flex flex-col">
                        <li class="fi-sidebar-group flex flex-col gap-y-1 fi-active">
                            <livewire:logout-menu />
                        </li>
                    </ul>
                ')
            )->userMenuItems([
                'logout' => Action::make('logout')
                    ->label('Logout')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->extraAttributes([
                        'data-logout-trigger' => true,
                    ]),
            ])->sidebarCollapsibleOnDesktop();
        });

        Panel::macro('autoDiscoverModuleResources', function (): Panel {
            /** @var Panel $panel */
            $panel = $this;

            concord()->getModules()->each(function (BaseBoxServiceProvider $module) use (&$panel) {
                $basePath = $module->getBasePath();
                $namespaceRoot = $module->getNamespaceRoot();
                $panel->discoverResources(
                    in: $basePath.'/Filament/Resources',
                    for: $namespaceRoot.'\\Filament\\Resources',
                );
                $panel->discoverPages(
                    in: $basePath.'/Filament/Pages',
                    for: $namespaceRoot.'\\Filament\\Pages',
                );
                $panel->discoverWidgets(
                    in: $basePath.'Filament/Widgets',
                    for: $namespaceRoot.'\\Filament\\Widgets',
                );
                $panel->discoverLivewireComponents(
                    in: $basePath.'Filament/Livewire',
                    for: $namespaceRoot.'\\Filament\\Livewire',
                );
            });

            return $panel;
        });
    }

    private function mergeStrMacro(): void
    {
        Str::macro('cleanPhoneNumber', function (?string $value) {
            return str_replace('+', '', $value);
        });
    }

    private function mergeArrMacro(): void
    {
        Arr::macro('snakeKeys', function ($array, $delimiter = '_') {
            $result = [];
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $value = Arr::snakeKeys($value, $delimiter);
                }
                $result[Str::snake($key, $delimiter)] = $value;
            }

            return $result;
        });
    }

    private function mergeRequestMacro(): void
    {
        Request::macro('perPage', function ($perPage = 10) {
            return (int) request()->input('per_page', request()->input('limit', $perPage));
        });
    }

    private function registerModuleLanguageFiles(): void
    {
        $concordModules = concord()->getModules();

        /** @var \Konekt\Concord\BaseBoxServiceProvider $provider */
        foreach ($concordModules as $moduleName => $provider) {
            $langDirectory = $provider->getBasePath().'/resources/lang';

            $moduleName = str_replace('appetiser.', '', $moduleName);
            if (is_dir($langDirectory)) {
                $this->loadTranslationsFrom($langDirectory, $moduleName);
            }
        }
    }
}
