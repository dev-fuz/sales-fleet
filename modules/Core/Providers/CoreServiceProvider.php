<?php
/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.3.1
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2023 KONKORD DIGITAL
 */

namespace Modules\Core\Providers;

use Akaunting\Money\Currency;
use Akaunting\Money\Money;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Console\PruneCommand as PruneModelCommand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Queue\Console\FlushFailedCommand;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Modules\Core\DatabaseState;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Facades\MailableTemplates;
use Modules\Core\Facades\Menu;
use Modules\Core\Facades\Zapier;
use Modules\Core\Menu\MenuItem;
use Modules\Core\Settings\SettingsMenu;
use Modules\Core\Settings\SettingsMenuItem;
use Modules\Core\Support\Media\PruneStaleMediaAttachments;
use Modules\Core\Support\Synchronization\Jobs\PeriodicSynchronizations;
use Modules\Core\Support\Synchronization\Jobs\RefreshWebhookSynchronizations;
use Modules\Core\Support\Timeline\Timelineables;
use Modules\Core\Updater\Migration;
use Modules\Core\Workflow\WorkflowEventsSubscriber;
use Modules\Core\Workflow\Workflows;

class CoreServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Core';

    protected string $moduleNameLower = 'core';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        $this->app->booted(function () {
            // Must be called before registering the "WorkflowEventsSubscriber" subscriber.
            Workflows::registerEventOnlyTriggersListeners();
            $this->app['events']->subscribe(WorkflowEventsSubscriber::class);
        });

        $this->app['events']->listen(RequestHandled::class, Workflows::processQueue(...));
        $this->app['events']->listen(RequestHandled::class, Zapier::processQueue(...));

        View::composer(
            ['core::app', 'core::components/layouts/skin'],
            \Modules\Core\Http\View\Composers\AppComposer::class
        );

        Currency::macro('toMoney', function (string|int|float $value, bool $convert = true) {
            /** @var \Akaunting\Money\Currency */
            $currency = $this;

            return new Money(! is_float($value) ? (float) $value : $value, $currency, $convert);
        });

        Innoclapps::whenReadyForServing(Timelineables::discover(...));
        Innoclapps::booting($this->registerMenuItems(...));
        Innoclapps::booting($this->registerSettingsMenuItems(...));

        $this->registerMacros();
        $this->registerCommands();

        $this->app->booted(function () {
            $this->scheduleTasks();
            Innoclapps::whenInstalled($this->configureBroadcasting(...));
        });
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        DatabaseState::register([
            \Modules\Core\Database\State\EnsureDefaultSettingsArePresent::class,
            \Modules\Core\Database\State\EnsureMailableTemplatesArePresent::class,
            \Modules\Core\Database\State\EnsureCountriesArePresent::class,
        ]);

        $this->app->singleton('timezone', \Modules\Core\Timezone::class);
        $this->app->when(Migration::class)->needs(Migrator::class)->give(fn () => $this->app['migrator']);
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'config/config.php'),
            $this->moduleNameLower
        );

        foreach (['html_purifier', 'fields', 'settings', 'updater', 'synchronization'] as $config) {
            $this->mergeConfigFrom(
                module_path($this->moduleName, "config/$config.php"),
                $config
            );
        }

    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->moduleNameLower);

        $sourcePath = module_path($this->moduleName, 'resources/views');

        $this->publishes([
            $sourcePath => $viewPath,
        ], ['views', $this->moduleNameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $this->loadTranslationsFrom(module_path($this->moduleName, 'resources/lang'), $this->moduleNameLower);
    }

    /**
     * Register the menu items.
     */
    protected function registerMenuItems(): void
    {
        Menu::register(MenuItem::make(__('core::dashboard.insights'), '/dashboard', 'ChartSquareBar')
            ->position(40));

        Menu::register(MenuItem::make(__('core::settings.settings'), '/settings', 'Cog')
            ->canSeeWhen('is-super-admin')
            ->position(100));
    }

    /**
     * Register the core settings menu items.
     */
    protected function registerSettingsMenuItems(): void
    {
        SettingsMenu::register(
            SettingsMenuItem::make(__('core::app.integrations'))->icon('Globe')->order(20)
                ->withChild(SettingsMenuItem::make('Pusher', '/settings/integrations/pusher'), 'pusher')
                ->withChild(SettingsMenuItem::make('Microsoft', '/settings/integrations/microsoft'), 'microsoft')
                ->withChild(SettingsMenuItem::make('Google', '/settings/integrations/google'), 'google')
                ->withChild(SettingsMenuItem::make('Zapier', '/settings/integrations/zapier'), 'zapier'),
            'integrations'
        );

        SettingsMenu::register(
            SettingsMenuItem::make(__('core::settings.security.security'))->icon('ShieldCheck')->order(60)
                ->withChild(SettingsMenuItem::make(__('core::settings.general'), '/settings/security'), 'security')
                ->withChild(SettingsMenuItem::make(__('core::settings.recaptcha.recaptcha'), '/settings/recaptcha'), 'recaptcha'),
            'security'
        );

        SettingsMenu::register(
            SettingsMenuItem::make(__('core::settings.system'))->icon('Cog')->order(70)
                ->withChild(SettingsMenuItem::make(__('core::update.update'), '/settings/update'), 'update')
                ->withChild(SettingsMenuItem::make(__('core::settings.tools.tools'), '/settings/tools'), 'tools')
                ->withChild(SettingsMenuItem::make(__('core::app.system_info'), '/settings/info'), 'system-info')
                ->withChild(SettingsMenuItem::make('Logs', '/settings/logs'), 'system-logs'),
            'system'
        );

        SettingsMenu::register(
            SettingsMenuItem::make(__('core::workflow.workflows'), '/settings/workflows', 'RocketLaunch')->order(40),
            'workflows'
        );

        SettingsMenu::register(
            SettingsMenuItem::make(__('core::mail_template.mail_templates'), '/settings/mailables', 'Mail')->order(50),
            'mailables'
        );

        tap(SettingsMenuItem::make(__('core::fields.fields'))->icon('SquaresPlus')->order(10), function ($item) {
            Innoclapps::registeredResources()
                ->filter(fn ($resource) => $resource::$fieldsCustomizable)
                ->each(function ($resource) use ($item) {
                    $item->withChild(
                        SettingsMenuItem::make(
                            $resource->singularLabel(),
                            "/settings/fields/{$resource->name()}"
                        ),
                        'fields-'.$resource->name()
                    );
                });
            SettingsMenu::register($item, 'fields');
        });
    }

    /**
     * Register the core commands.
     */
    public function registerCommands(): void
    {
        $this->commands([
            \Modules\Core\Console\Commands\ClearExcelTmpPathCommand::class,
            \Modules\Core\Console\Commands\ClearHtmlPurifierCacheCommand::class,
            \Modules\Core\Console\Commands\ClearUpdaterTmpPathCommand::class,
            \Modules\Core\Console\Commands\FinalizeUpdateCommand::class,
            \Modules\Core\Console\Commands\IdentificationKeyGenerateCommand::class,
            \Modules\Core\Console\Commands\UpdateCommand::class,
        ]);
    }

    /**
     * Schedule the document related tasks.
     */
    public function scheduleTasks(): void
    {
        /** @var \Illuminate\Console\Scheduling\Schedule */
        $schedule = $this->app->make(Schedule::class);

        $schedule->call(new PruneStaleMediaAttachments)->name('prune-stale-media-attachments')->daily();
        $schedule->job(PeriodicSynchronizations::class)->cron(config('synchronization.interval'));
        $schedule->job(RefreshWebhookSynchronizations::class)->daily();

        $schedule
            ->call(function () {
                settings()->set([
                    '_last_cron_run' => now(),
                    '_cron_job_last_user' => get_current_process_user(),
                    '_cron_php_version' => PHP_VERSION,
                ])->save();
            })
            ->name('capture-cron-environment')
            ->everyMinute();

        $schedule
            ->call(function () {
                MailableTemplates::seedIfRequired();
            })
            ->name('seed-mailable-templates')
            ->daily();

        if (Innoclapps::canRunProcess()) {
            $schedule->command(PruneModelCommand::class)->daily();
            $schedule->command(FlushFailedCommand::class)->weekly();
        } else {
            $schedule->call(function () {
                Artisan::call(PruneModelCommand::class);
            })->daily();

            $schedule->call(function () {
                Artisan::call(FlushFailedCommand::class);
            })->weekly();
        }
    }

    /**
     * Register application macros
     */
    public function registerMacros(): void
    {
        Request::macro('isForTimeline', fn () => Request::boolean('timeline'));

        Request::macro('getWith', function () {
            return Str::of(Request::instance()->get('with', ''))->explode(';')->filter()->all();
        });

        Str::macro('isBase64Encoded', new \Modules\Core\Support\Macros\Str\IsBase64Encoded);
        Str::macro('clickable', new \Modules\Core\Support\Macros\Str\ClickableUrls);

        Arr::macro('toObject', new \Modules\Core\Support\Macros\Arr\ToObject);
        Arr::macro('valuesAsString', new \Modules\Core\Support\Macros\Arr\CastValuesAsString);

        Request::macro('isSearching', new \Modules\Core\Support\Macros\Request\IsSearching);
        Request::macro('isZapier', new \Modules\Core\Support\Macros\Request\IsZapier);

        DB::macro('listIndexes', function (string|Model $table, string $column = null) {
            $tableName = $table instanceof Model ? $table->getTable() : $table;
            $prefix = $table instanceof Model ? $table->getConnection()->getTablePrefix() : DB::getTablePrefix();

            $query = 'SHOW KEYS FROM '.$prefix.$tableName.'';

            if ($column) {
                $query .= ' WHERE Column_name=\''.$column.'\'';
            }

            return DB::select(DB::raw($query)->getValue(DB::connection()->getQueryGrammar()));
        });

        Filesystem::macro('deepCleanDirectory', new \Modules\Core\Support\Macros\Filesystem\DeepCleanDirectory);

        \Modules\Core\Support\Macros\Criteria\QueryCriteria::register();

        Collection::macro('trim', function ($character_mask = " \t\n\r\0\x0B") {
            /** @var \Illuminate\Support\Collection */
            $collection = $this;

            return $collection->map(function ($value) use ($character_mask) {
                return trim($value, $character_mask);
            });
        });

        URL::macro('asAppUrl', function ($extra = '') {
            return rtrim(config('app.url'), '/').($extra ? '/'.$extra : '');
        });
    }

    /**
     * Set the broadcasting driver
     */
    protected function configureBroadcasting(): void
    {
        if (Innoclapps::hasBroadcastingConfigured()) {
            $this->app['config']->set('broadcasting.default', 'pusher');
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    /**
     * Get the publishable view paths.
     */
    private function getPublishableViewPaths(): array
    {
        $paths = [];

        foreach ($this->app['config']->get('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->moduleNameLower)) {
                $paths[] = $path.'/modules/'.$this->moduleNameLower;
            }
        }

        return $paths;
    }
}
