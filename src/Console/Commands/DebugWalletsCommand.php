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
        $this->info('Debugging Wallets Module...');

        // Check route file loading
        $this->info("\n Route Files:");
        $moduleRoutes = base_path('Modules/Wallets/routes/web.php');
        if (File::exists($moduleRoutes)) {
            $this->info("Module routes found: {$moduleRoutes}");
            $this->info("Last modified: " . date('Y-m-d H:i:s', filemtime($moduleRoutes)));
        } else {
            $this->error(" Module routes not found");
        }

        $packageRoutes = base_path('packages/admin/wallets/src/routes/web.php');
        if (File::exists($packageRoutes)) {
            $this->info(" Package routes found: {$packageRoutes}");
            $this->info(" Last modified: " . date('Y-m-d H:i:s', filemtime($packageRoutes)));
        } else {
            $this->error(" Package routes not found");
        }
        
        // Check view loading priority
        $this->info("\n View Loading Priority:");
        $viewPaths = [
            'Module views' => base_path('Modules/Wallets/resources/views'),
            'Published views' => resource_path('views/admin/wallet'),
            'Package views' => base_path('packages/admin/wallets/resources/views'),
        ];
        
        foreach ($viewPaths as $name => $path) {
            if (File::exists($path)) {
                $this->info(" {$name}: {$path}");
            } else {
                $this->warn(" {$name}: NOT FOUND - {$path}");
            }
        }
        
        // Check controller resolution
        $this->info("\n Controller Resolution:");
        $controllers = [
            'Modules\\Wallets\\app\\Http\\Controllers\\Admin\\TransactionManagerController',
            'Modules\\Wallets\\app\\Http\\Controllers\\Admin\\WithdrawManagerController',
        ];
        foreach ($controllers as $controllerClass) {
            if (class_exists($controllerClass)) {
                $this->info(" Controller class exists: {$controllerClass}");
            } else {
                $this->error(" Controller class not found: {$controllerClass}");
            }
        }

        // Check model resolution
        $this->info("\n Model Resolution:");
        $models = [
            'Modules\\Wallets\\app\\Models\\Wallet',
            'Modules\\Wallets\\app\\Models\\WalletTransaction',
            'Modules\\Wallets\\app\\Models\\WithdrawRequest',
        ];
        
        foreach ($models as $modelClass) {
            if (class_exists($modelClass)) {
                $this->info(" Model class exists: {$modelClass}");
            } else {
                $this->error(" Model class not found: {$modelClass}");
            }
        }

        $this->info("\n Recommendations:");
        $this->info("- Module files take priority over package files");
        $this->info("- If module view doesn't exist, it will fallback to package view");
    }
}
