<?php

namespace admin\wallets;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class WalletServiceProvider extends ServiceProvider
{
    public function boot()
    {
        /**
         * Load views
         * Priority:
         *  1. Published overrides: resources/views/vendor/wallet
         *  2. Module views: Modules/Wallets/resources/views
         *  3. Package views: packages/admin/wallets/resources/views
         */
        $this->loadViewsFrom([
            resource_path('views/vendor/wallet'),
            base_path('Modules/Wallets/resources/views'),
            __DIR__ . '/../resources/views',
        ], 'wallet');

        /**
         * Merge config
         */
        $this->mergeConfigFrom(__DIR__ . '/../config/wallet.php', 'wallet');

        /**
         * Load migrations from both package + module
         */
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if (is_dir(base_path('Modules/Wallets/database/migrations'))) {
            $this->loadMigrationsFrom(base_path('Modules/Wallets/database/migrations'));
        }

        /**
         * Publish package files for customization
         */
        $this->publishes([
            __DIR__ . '/../config/wallet.php'      => config_path('wallet.php'),
            __DIR__ . '/../database/migrations'    => base_path('Modules/Wallets/database/migrations'),
            __DIR__ . '/../resources/views'        => resource_path('views/vendor/wallet'),
        ], 'wallet');

        $this->publishes([
            __DIR__ . '/../resources/views'        => base_path('Modules/Wallets/resources/views'),
        ], 'wallet');

        /**
         * Register routes
         */
        $this->registerAdminRoutes();
        $this->registerApiRoutes();
    }

    protected function registerAdminRoutes()
    {
        // Avoid errors before migrations are applied
        if (!Schema::hasTable('admins')) {
            return;
        }

        $admin = DB::table('admins')->orderBy('created_at', 'asc')->first();
        $slug = $admin->website_slug ?? 'admin';

        Route::middleware('web')
            ->prefix("{$slug}/admin")
            ->group(function () {
                if (file_exists(base_path('Modules/Wallets/routes/web.php'))) {
                    $this->loadRoutesFrom(base_path('Modules/Wallets/routes/web.php'));
                } else {
                    $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
                }
            });
    }

    protected function registerApiRoutes()
    {
        Route::middleware('api')
            ->prefix('api/v1/wallets')
            ->group(function () {
                if (file_exists(base_path('Modules/Wallets/routes/api.php'))) {
                    $this->loadRoutesFrom(base_path('Modules/Wallets/routes/api.php'));
                } else {
                    $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
                }
            });
    }

    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \admin\wallets\Console\Commands\PublishWalletsModuleCommand::class,
                \admin\wallets\Console\Commands\CheckModuleStatusCommand::class,
                \admin\wallets\Console\Commands\DebugWalletsCommand::class,
            ]);
        }
    }
}
