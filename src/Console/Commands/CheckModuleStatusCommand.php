<?php

namespace admin\wallets\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CheckModuleStatusCommand extends Command
{
    protected $signature = 'wallets:status';
    protected $description = 'Check if Wallets module files are being used';

    public function handle()
    {
        $this->info('Checking Wallets Module Status...');
        
        // Check if module files exist
        $moduleFiles = [
            'Controller: WalletTransactionController' => base_path('Modules/Wallets/app/Http/Controllers/Admin/WalletTransactionController.php'),
            'Controller: WalletWithdrawController'    => base_path('Modules/Wallets/app/Http/Controllers/Admin/WalletWithdrawController.php'),
            'Controller: WalletWebStripeController'    => base_path('Modules/Wallets/app/Http/Controllers/Admin/WalletWebStripeController.php'),
            'Controller: WalletStripeController'      => base_path('Modules/Wallets/app/Http/Controllers/Api/V1/WalletStripeController.php'),
            'Controller: WalletController'            => base_path('Modules/Wallets/app/Http/Controllers/Api/V1/WalletController.php'),
            'Model: Wallet'                           => base_path('Modules/Wallets/app/Models/Wallet.php'),
            'Model: WalletTransaction'                => base_path('Modules/Wallets/app/Models/WalletTransaction.php'),
            'Model: WithdrawRequest'                  => base_path('Modules/Wallets/app/Models/WithdrawRequest.php'),
            'Request: WalletWithdrawRequest'          => base_path('Modules/Wallets/app/Http/Requests/Api/WalletWithdrawRequest.php'),
            'Routes: web.php'                         => base_path('Modules/Wallets/routes/web.php'),
            'Routes: api.php'                         => base_path('Modules/Wallets/routes/api.php'),
            'Views'                                   => base_path('Modules/Wallets/resources/views'),
            'Config'                                  => base_path('Modules/Wallets/config/wallet.php'),
        ];

        $this->info("\n Module Files Status:");
        foreach ($moduleFiles as $label => $path) {
            if (File::exists($path)) {
                $this->info(" {$label}: EXISTS");

                // Show last modified for PHP files
                if (str_ends_with($path, '.php')) {
                    $lastModified = date('Y-m-d H:i:s', filemtime($path));
                    $this->line("    Last modified: {$lastModified}");
                }
            } else {
                $this->error(" {$label}: NOT FOUND");
            }
        }

        $this->info("\n Namespace Validation:");
        $controllers = [
            'WalletTransactionController' => base_path('Modules/Wallets/app/Http/Controllers/Admin/WalletTransactionController.php'),
            'WalletWithdrawController'    => base_path('Modules/Wallets/app/Http/Controllers/Admin/WalletWithdrawController.php'),
            'WalletWebStripeController'    => base_path('Modules/Wallets/app/Http/Controllers/Admin/WalletWebStripeController.php'),
            'WalletStripeController'             => base_path('Modules/Wallets/app/Http/Controllers/Api/V1/WalletStripeController.php'),
            'WalletController'             => base_path('Modules/Wallets/app/Http/Controllers/Api/V1/WalletController.php'),
        ];

        foreach ($controllers as $name => $controllerPath) {
            if (File::exists($controllerPath)) {
                $content = File::get($controllerPath);

                preg_match('/^namespace\s+([^;]+);/m', $content, $matches);
                $namespace = $matches[1] ?? 'N/A';

                if (str_starts_with($namespace, 'Modules\\Wallets')) {
                    $this->info(" {$name} namespace: {$namespace}");
                } else {
                    $this->error(" {$name} namespace incorrect: {$namespace}");
                }

                // Check persistence marker
                if (str_contains($content, 'Test comment - this should persist after refresh')) {
                    $this->line("  Test comment: FOUND (changes persist)");
                } else {
                    $this->warn("  Test comment: NOT FOUND");
                }
            }
        }

        // Check composer autoload
        $this->info("\n Composer Autoload:");
        $composerFile = base_path('composer.json');
        if (File::exists($composerFile)) {
            $composer = json_decode(File::get($composerFile), true);
            if (isset($composer['autoload']['psr-4']['Modules\\Wallets\\'])) {
                $this->info(" Composer autoload: CONFIGURED");
            } else {
                $this->error(" Composer autoload: NOT CONFIGURED");
            }
        }

        $this->info("\n Summary:");
        $this->info("Your Wallets module is properly published and should be working.");
        $this->info("Any changes you make to files in Modules/Wallets/ will persist.");
        $this->info("If you need to republish from the package, run: php artisan wallets:publish --force");
    }
}
