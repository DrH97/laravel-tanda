<?php

namespace DrH\Tanda;

use DrH\Tanda\Console\InstallCommand;
use DrH\Tanda\Console\RequestStatusCommand;
use DrH\Tanda\Library\BaseClient;
use DrH\Tanda\Library\Utility;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class TandaServiceProvider extends ServiceProvider
{
    /**
     * Registers the Tanda service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/tanda.php', 'tanda');

//        TODO: Change this to bind for a stateless sort of lib
        $this->app->singleton(BaseClient::class, function () {
            return new BaseClient(new Client(['http_errors' => false]));
        });
    }


    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/Http/routes.php');
        $this->publishes([
            __DIR__ . '/../config/tanda.php' => config_path('tanda.php'),
        ], 'tanda-config');

        $this->registerMigrations();

        $this->registerCommands();

        $this->registerFacades();

        $this->requireHelperScripts();
    }

    /**
     * Register facade accessors
     */
    private function registerFacades()
    {
//        IMPORTANT: Facades are with FQDN. Concrete/Implementations are imported, else there could be an error
        $this->app->singleton(
            Facades\Utility::class,
            function ($app) {
                return $this->app->make(Utility::class);
            }
        );
    }

    /**
     * Register the package's migrations.
     *
     * @return void
     */
    protected function registerMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    /**
     * Register the package's commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                RequestStatusCommand::class
            ]);
        }
    }

    private function requireHelperScripts()
    {
        $files = glob(__DIR__ . '/Support/*.php');
        foreach ($files as $file) {
            include_once $file;
        }
    }
}
