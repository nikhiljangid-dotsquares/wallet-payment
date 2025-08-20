<?php

namespace admin\wallets\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishWalletsModuleCommand extends Command
{
    protected $signature = 'wallets:publish {--force : Force overwrite existing files}';
    protected $description = 'Publish Wallets module files with proper namespace transformation';

    public function handle()
    {
        $this->info('Publishing Wallets module files...');

        // Ensure module base directory exists
        $moduleDir = base_path('Modules/Wallets');
        File::ensureDirectoryExists($moduleDir);

        // Publish PHP files with namespace transformation
        $this->publishWithNamespaceTransformation();

        // Publish config & views from package (if defined in service provider)
        $this->callSilent('vendor:publish', [
            '--tag'   => 'wallet',
            '--force' => $this->option('force')
        ]);

        // Update composer autoload
        $this->updateComposerAutoload();

        $this->info("\n✅ Wallets module published successfully!");
        $this->info("👉 Run: composer dump-autoload");
    }

    protected function publishWithNamespaceTransformation()
    {
        $basePath = dirname(dirname(__DIR__)); // .../wallets/src

        $files = [
            // Controllers Admin
            $basePath . '/Controllers/Admin/WalletTransactionController.php' => base_path('Modules/Wallets/app/Http/Controllers/Admin/WalletTransactionController.php'),
            $basePath . '/Controllers/Admin/WalletWithdrawController.php'   => base_path('Modules/Wallets/app/Http/Controllers/Admin/WalletWithdrawController.php'),

            // Controllers Api
            $basePath . '/Controllers/Api/V1/WelletStripeController.php' => base_path('Modules/Wallets/app/Http/Controllers/Api/V1/WelletStripeController.php'),
            $basePath . '/Controllers/Api/V1/WalletController.php' => base_path('Modules/Wallets/app/Http/Controllers/Api/V1/WalletController.php'),

            // Models
            $basePath . '/Models/Wallet.php'            => base_path('Modules/Wallets/app/Models/Wallet.php'),
            $basePath . '/Models/WalletTransaction.php' => base_path('Modules/Wallets/app/Models/WalletTransaction.php'),
            $basePath . '/Models/WithdrawRequest.php'   => base_path('Modules/Wallets/app/Models/WithdrawRequest.php'),

            // Routes
            $basePath . '/routes/web.php' => base_path('Modules/Wallets/routes/web.php'),
            $basePath . '/routes/api.php' => base_path('Modules/Wallets/routes/api.php'),
        ];

        foreach ($files as $source => $destination) {
            if (!File::exists($source)) {
                $this->warn("⚠️ Source file not found: {$source}");
                continue;
            }

            File::ensureDirectoryExists(dirname($destination));

            $content = File::get($source);
            $content = $this->transformNamespaces($content, $destination);

            File::put($destination, $content);
            $this->info("✅ Published: " . basename($destination));
        }
    }

    protected function transformNamespaces(string $content, string $destination): string
    {
        // Decide namespace based on destination path
        if (str_contains($destination, '/Controllers/Admin/')) {
            $content = str_replace(
                'namespace admin\\wallets\\Controllers\\Admin;',
                'namespace Modules\\Wallets\\app\\Http\\Controllers\\Admin;',
                $content
            );
        } elseif (str_contains($destination, '/Controllers/Api/')) {
            $content = str_replace(
                'namespace admin\\wallets\\Controllers\\Api\\V1;',
                'namespace Modules\\Wallets\\app\\Http\\Controllers\\Api\\V1;',
                $content
            );
        } elseif (str_contains($destination, '/Models/')) {
            $content = str_replace(
                'namespace admin\\wallets\\Models;',
                'namespace Modules\\Wallets\\app\\Models;',
                $content
            );
        }

        // Replace use statements
        $content = str_replace('use admin\\wallets\\Models\\', 'use Modules\\Wallets\\app\\Models\\', $content);
        $content = str_replace('use admin\\wallets\\Requests\\', 'use Modules\\Wallets\\app\\Http\\Requests\\', $content);

        // Fix route controller references
        $content = str_replace(
            'admin\\wallets\\Controllers\\WalletTransactionController',
            'Modules\\Wallets\\app\\Http\\Controllers\\Admin\\WalletTransactionController',
            $content
        );
        $content = str_replace(
            'admin\\wallets\\Controllers\\WalletWithdrawController',
            'Modules\\Wallets\\app\\Http\\Controllers\\Admin\\WalletWithdrawController',
            $content
        );
        $content = str_replace(
            'admin\\wallets\\Controllers\\WelletStripeController',
            'Modules\\Wallets\\app\\Http\\Controllers\\Api\\V1\\WelletStripeController',
            $content
        );
        $content = str_replace(
            'admin\\wallets\\Controllers\\WalletController',
            'Modules\\Wallets\\app\\Http\\Controllers\\Api\\V1\\WalletController',
            $content
        );

        return $content;
    }

    protected function updateComposerAutoload()
    {
        $composerFile = base_path('composer.json');
        $composer = json_decode(File::get($composerFile), true);

        if (!isset($composer['autoload']['psr-4']['Modules\\Wallets\\'])) {
            $composer['autoload']['psr-4']['Modules\\Wallets\\'] = 'Modules/Wallets/app/';
            File::put($composerFile, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->info("🔄 Updated composer.json autoload (Modules\\Wallets\\)");
        }
    }
}
