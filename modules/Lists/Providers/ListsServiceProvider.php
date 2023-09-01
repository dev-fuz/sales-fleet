<?php

namespace Modules\Lists\Providers;

use Illuminate\Support\Facades\Log;
use Modules\Core\Facades\Innoclapps;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Settings\SettingsMenu;
use Modules\Core\Settings\SettingsMenuItem;

class ListsServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Lists';

    protected string $moduleNameLower = 'lists';

    /**
     * Boot the application events.
     */
    public function boot() : void
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        $this->app->booted(function () {
            Innoclapps::whenReadyForServing($this->bootModule(...));
        });

    }

    /**
     * Register the service providers.
     */
    public function register() : void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     */
    protected function registerConfig() : void
    {
        $this->publishes([
            module_path($this->moduleName, 'config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');

        $this->mergeConfigFrom(
            module_path($this->moduleName, 'config/config.php'), $this->moduleNameLower
        );
    }

    /**
     * Register views.
     */
    public function registerViews() : void
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);

        $sourcePath = module_path($this->moduleName, 'resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    /**
     * Register translations.
     */
    public function registerTranslations() : void
    {
        $this->loadTranslationsFrom(module_path($this->moduleName, 'resources/lang'), $this->moduleNameLower);
    }

    /**
     * Boot the module
     */
    protected function bootModule() : void
    {
        Innoclapps::booting($this->shareDataToScript(...));

        // Register lists menu in settings
        Innoclapps::booting(function () {
            SettingsMenu::register(
                SettingsMenuItem::make("Lists", '/settings/lists', 'Bars3CenterLeft')->order(12),
                'lists'
            );
        });


        Log::info('Testing 1');
    }

    /**
     * Share data to script.
     */
    protected function shareDataToScript() : void
    {
        Innoclapps::provideToScript([
            'lists' => []
        ]);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides() : array
    {
        return [];
    }

    /**
     * Get the publishable view paths.
     */
    private function getPublishableViewPaths() : array
    {
        $paths = [];

        foreach ($this->app['config']->get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }

        return $paths;
    }
}
