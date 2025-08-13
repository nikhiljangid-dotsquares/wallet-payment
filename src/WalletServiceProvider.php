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
        // Load Transactions Views
        $this->loadViewsFrom([
            base_path('Modules/Wallets/resources/views/wallets'),
            resource_path('views/admin/wallets'),
            __DIR__ . '/../resources/views/wallets'
        ], 'wallets');

        $this->mergeConfigFrom(__DIR__.'/../config/wallet.php', 'wallet.constants');
        $this->mergeConfigFrom(__DIR__ . '/../config/wallet.php', 'wallet.config');

        // Also merge config from published module if it exists
        if (file_exists(base_path('Modules/Wallets/config/wallets.php'))) {
            $this->mergeConfigFrom(base_path('Modules/Wallets/config/wallets.php'), 'wallet.config');
        }
        
        // Also register module views with a specific namespace for explicit usage
        if (is_dir(base_path('Modules/Wallets/resources/views'))) {
            $this->loadViewsFrom(base_path('Modules/Wallets/resources/views'), 'wallets-module');
        }
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        // Also load migrations from published module if they exist
        if (is_dir(base_path('Modules/Wallets/database/migrations'))) {
            $this->loadMigrationsFrom(base_path('Modules/Wallets/database/migrations'));
        }
        
        // Only publish automatically during package installation, not on every request
        // Use 'php artisan wallets:publish' command for manual publishing
        // $this->publishWithNamespaceTransformation();
        
        // Standard publishing for non-PHP files
        $this->publishes([
            __DIR__ . '/../config/' => base_path('Modules/Wallets/config/'),
            __DIR__ . '/../database/migrations' => base_path('Modules/Wallets/database/migrations'),
            __DIR__ . '/../resources/views' => base_path('Modules/Wallets/resources/views/'),
        ], 'wallet');
       
        $this->registerAdminRoutes();
    }

    protected function registerAdminRoutes()
    {
        if (!Schema::hasTable('admins')) {
            return; // Avoid errors before migration
        }

        $admin = DB::table('admins')
            ->orderBy('created_at', 'asc')
            ->first();
            
        $slug = $admin->website_slug ?? 'admin';

        Route::middleware('web')
            ->prefix("{$slug}/admin") // dynamic prefix
            ->group(function () {
                // Load routes from published module first, then fallback to package
                if (file_exists(base_path('Modules/Wallets/routes/web.php'))) {
                    $this->loadRoutesFrom(base_path('Modules/Wallets/routes/web.php'));
                } else {
                    $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
                }
            });
    }

    public function register()
    {
        // Register the publish command
        if ($this->app->runningInConsole()) {
            $this->commands([
                \admin\wallets\Console\Commands\PublishWalletsModuleCommand::class,
                \admin\wallets\Console\Commands\CheckModuleStatusCommand::class,
                \admin\wallets\Console\Commands\DebugWalletsCommand::class,
            ]);
        }
    }

    /**
     * Publish files with namespace transformation
     */
    protected function publishWithNamespaceTransformation()
    {
        // Define the files that need namespace transformation
        $filesWithNamespaces = [
            // Controllers
            __DIR__ . '/../src/Controllers/TransactionManagerController.php' => base_path('Modules/Wallets/app/Http/Controllers/Admin/TransactionManagerController.php'),

            __DIR__ . '/../src/Controllers/WithdrawManagerController.php' => base_path('Modules/Wallets/app/Http/Controllers/Admin/WithdrawManagerController.php'),
            
            // Models
            __DIR__ . '/../src/Models/Wallet.php' => base_path('Modules/Wallets/app/Models/Wallet.php'),
            __DIR__ . '/../src/Models/WalletTransaction.php' => base_path('Modules/Wallets/app/Models/WalletTransaction.php'),
            __DIR__ . '/../src/Models/WithdrawRequest.php' => base_path('Modules/Wallets/app/Models/WithdrawRequest.php'),
            
            // Routes
            __DIR__ . '/routes/web.php' => base_path('Modules/Wallets/routes/web.php'),
        ];

        foreach ($filesWithNamespaces as $source => $destination) {
            if (File::exists($source)) {
                // Create destination directory if it doesn't exist
                File::ensureDirectoryExists(dirname($destination));
                
                // Read the source file
                $content = File::get($source);
                
                // Transform namespaces based on file type
                $content = $this->transformNamespaces($content, $source);
                
                // Write the transformed content to destination
                File::put($destination, $content);
            }
        }
    }

    /**
     * Transform namespaces in PHP files
     */
    protected function transformNamespaces($content, $sourceFile)
    {
        // Define namespace mappings
        $namespaceTransforms = [
            // Main namespace transformations
            'namespace admin\\wallets\\Controllers;' => 'namespace Modules\\Wallets\\app\\Http\\Controllers\\Admin;',
            'namespace admin\\wallets\\Models;' => 'namespace Modules\\Wallets\\app\\Models;',
            'namespace admin\\wallets\\Requests;' => 'namespace Modules\\Wallets\\app\\Http\\Requests;',
            
            // Use statements transformations
            'use admin\\wallets\\Controllers\\' => 'use Modules\\Wallets\\app\\Http\\Controllers\\Admin\\',
            'use admin\\wallets\\Models\\' => 'use Modules\\Wallets\\app\\Models\\',
            'use admin\\wallets\\Requests\\' => 'use Modules\\Wallets\\app\\Http\\Requests\\',
            
            // Class references in routes
            'admin\\wallets\\Controllers\\TransactionManagerController' => 'Modules\\Wallets\\app\\Http\\Controllers\\Admin\\TransactionManagerController',

            'admin\\wallets\\Controllers\\WithdrawManagerController' => 'Modules\\Wallets\\app\\Http\\Controllers\\Admin\\WithdrawManagerController',
        ];

        // Apply transformations
        foreach ($namespaceTransforms as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        // Handle specific file types
        if (str_contains($sourceFile, 'Controllers')) {
            $content = $this->transformControllerNamespaces($content);
        } elseif (str_contains($sourceFile, 'Models')) {
            $content = $this->transformModelNamespaces($content);
        } elseif (str_contains($sourceFile, 'Requests')) {
            $content = $this->transformRequestNamespaces($content);
        } elseif (str_contains($sourceFile, 'routes')) {
            $content = $this->transformRouteNamespaces($content);
        }

        return $content;
    }

    /**
     * Transform controller-specific namespaces
     */
    protected function transformControllerNamespaces($content)
    {
        // Update use statements for models and requests
        $content = str_replace(
            'use admin\\wallets\\Models\\Wallet;',
            'use Modules\\Wallets\\app\\Models\\Wallet;',
            $content
        );
        
        $content = str_replace(
            'use admin\\wallets\\Models\\WalletTransaction;',
            'use Modules\\Wallets\\app\\Models\\WalletTransaction;',
            $content
        );

        $content = str_replace(
            'use admin\\wallets\\Models\\WithdrawRequest;',
            'use Modules\\Wallets\\app\\Models\\WithdrawRequest;',
            $content
        );

        $content = str_replace(
            'use admin\\wallets\\Requests\\CategoryCreateRequest;',
            'use Modules\\Wallets\\app\\Http\\Requests\\CategoryCreateRequest;',
            $content
        );
        
        $content = str_replace(
            'use admin\\wallets\\Requests\\CategoryUpdateRequest;',
            'use Modules\\Wallets\\app\\Http\\Requests\\CategoryUpdateRequest;',
            $content
        );

        return $content;
    }

    /**
     * Transform model-specific namespaces
     */
    protected function transformModelNamespaces($content)
    {
        // Any model-specific transformations
        return $content;
    }

    /**
     * Transform request-specific namespaces
     */
    protected function transformRequestNamespaces($content)
    {
        // Any request-specific transformations
        return $content;
    }

    /**
     * Transform route-specific namespaces
     */
    protected function transformRouteNamespaces($content)
    {
        // Update controller references in routes
        $content = str_replace(
            'admin\\wallets\\Controllers\\TransactionManagerController',
            'Modules\\Wallets\\app\\Http\\Controllers\\Admin\\TransactionManagerController',
            $content
        );

         $content = str_replace(
            'admin\\wallets\\Controllers\\WithdrawManagerController',
            'Modules\\Wallets\\app\\Http\\Controllers\\Admin\\WithdrawManagerController',
            $content
        );

        return $content;
    }
}
