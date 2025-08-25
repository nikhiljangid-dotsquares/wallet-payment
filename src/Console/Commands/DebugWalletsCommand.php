<?php

namespace admin\wallets\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DebugWalletsCommand extends Command
{
    protected $signature = 'wallets:debug';
    protected $description = 'Debug Wallets module routing and view resolution';

    public function handle()
    {
        $this->info(' Debugging Wallets Module...');

        // Check route files
        $this->info("\n Route Files:");
        $routeFiles = [
            'Module web.php'    => base_path('Modules/Wallets/routes/web.php'),
            'Module api.php'    => base_path('Modules/Wallets/routes/api.php'),
            'Package web.php'   => base_path('packages/admin/wallets/src/routes/web.php'),
            'Package api.php'   => base_path('packages/admin/wallets/src/routes/api.php'),
        ];

        foreach ($routeFiles as $label => $path) {
            if (File::exists($path)) {
                $this->info(" {$label}: FOUND");
                $this->line(" Path: {$path}");
                $this->line(" Last modified: " . date('Y-m-d H:i:s', filemtime($path)));
            } else {
                $this->error(" {$label}: NOT FOUND ({$path})");
            }
        }

        // Check view loading priority
        $this->info("\n View Loading Priority:");
        $viewPaths = [
            'Module views'    => base_path('Modules/Wallets/resources/views'),
            'Published views' => resource_path('views/admin/wallet'),
            'Package views'   => base_path('packages/admin/wallets/resources/views'),
        ];

        foreach ($viewPaths as $label => $path) {
            if (File::isDirectory($path)) {
                $this->info(" {$label}: EXISTS ({$path})");
            } else {
                $this->warn(" {$label}: NOT FOUND ({$path})");
            }
        }

        // Check controllers
        $this->info("\n Controller Resolution:");
        $controllers = [
            'Modules\\Wallets\\App\\Http\\Controllers\\Admin\\WalletTransactionController',
            'Modules\\Wallets\\App\\Http\\Controllers\\Admin\\WalletWithdrawController',
            'Modules\\Wallets\\App\\Http\\Controllers\\Admin\\WalletWebStripeController',
            'Modules\\Wallets\\App\\Http\\Controllers\\Api\\V1\\WelletStripeController',
            'Modules\\Wallets\\App\\Http\\Controllers\\Api\\V1\\WalletController',
        ];

        foreach ($controllers as $class) {
            if (class_exists($class)) {
                $this->info(" Controller class exists: {$class}");
            } else {
                $this->error(" Controller class NOT FOUND: {$class}");
            }
        }

        // Check models
        $this->info("\n Model Resolution:");
        $models = [
            'Modules\\Wallets\\app\\Models\\Wallet',
            'Modules\\Wallets\\app\\Models\\WalletTransaction',
            'Modules\\Wallets\\app\\Models\\WithdrawRequest',
        ];

        foreach ($models as $class) {
            if (class_exists($class)) {
                $this->info(" Model class exists: {$class}");
            } else {
                $this->error(" Model class NOT FOUND: {$class}");
            }
        }

        // Check requests
        $this->info("\n Request Resolution:");
        $requests = [
            'Modules\\Wallets\\app\\Http\\Requests\\Api\\WalletWithdrawRequest',
        ];

        foreach ($requests as $class) {
            if (class_exists($class)) {
                $this->info(" Request class exists: {$class}");
            } else {
                $this->error(" Request class NOT FOUND: {$class}");
            }
        }

        // Recommendations
        $this->info("\n Recommendations:");
        $this->line("- Module files take priority over package files if both exist.");
        $this->line("- If a view is missing in the module, Laravel will fallback to package view.");
        $this->line("- If controllers/models are not found, check:");
        $this->line("   • Namespace in the PHP file matches PSR-4 autoload (Modules\\Wallets\\App\\...).");
        $this->line("   • Run `composer dump-autoload` to refresh class map.");
        $this->line("   • Ensure module is registered in `composer.json` autoload.");
    }
}
