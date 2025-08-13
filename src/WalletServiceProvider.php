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
         * - First priority: published views in `resources/views/vendor/wallets`
         * - Second priority: package views in `wallets/resources/views`
         */
        $this->loadViewsFrom([
            resource_path('views/vendor/wallets'),   // if developer publishes & overrides views
            __DIR__ . '/../wallets/resources/views' // package's own view folder
        ], 'wallets');

        /**
         * Merge config
         */
        $this->mergeConfigFrom(__DIR__ . '/../config/wallet.php', 'wallet');

        /**
         * Load migrations from package + published module
         */
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if (is_dir(base_path('Modules/Wallets/database/migrations'))) {
            $this->loadMigrationsFrom(base_path('Modules/Wallets/database/migrations'));
        }

        /**
         * Publish package files for customization
         */
        $this->publishes([
            __DIR__ . '/../config/' => base_path('Modules/Wallets/config/'),
            __DIR__ . '/../database/migrations' => base_path('Modules/Wallets/database/migrations'),
            __DIR__ . '/../resources/views' => base_path('Modules/Wallets/resources/views/'),
        ], 'wallet');

        /**
         * Register admin routes
         */
        $this->registerAdminRoutes();
    }

    protected function registerAdminRoutes()
    {
        if (!Schema::hasTable('admins')) {
            return; // Avoid errors before migrations are run
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
