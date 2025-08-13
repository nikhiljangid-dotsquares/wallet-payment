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

        // Check if module directory exists
        $moduleDir = base_path('Modules/Wallets');
        if (!File::exists($moduleDir)) {
            File::makeDirectory($moduleDir, 0755, true);
        }

        // Publish with namespace transformation
        $this->publishWithNamespaceTransformation();
        
        // Publish other files
        $this->call('vendor:publish', [
            '--tag' => 'wallet',
            '--force' => $this->option('force')
        ]);

        // Update composer autoload
        $this->updateComposerAutoload();

        $this->info('Wallets module published successfully!');
        $this->info('Please run: composer dump-autoload');
    }

    protected function publishWithNamespaceTransformation()
    {
        $basePath = dirname(dirname(__DIR__)); // Go up to packages/admin/wallets/src
        
        $filesWithNamespaces = [
            // Controllers
            $basePath . '/Controllers/TransactionManagerController.php' => base_path('Modules/Wallets/app/Http/Controllers/Admin/TransactionManagerController.php'),
            
            // Models
            $basePath . '/Models/Wallet.php' => base_path('Modules/Wallets/app/Models/Wallet.php'),
            $basePath . '/Models/WalletTransaction.php' => base_path('Modules/Wallets/app/Models/WalletTransaction.php'),
            $basePath . '/Models/WithdrawRequest.php' => base_path('Modules/Wallets/app/Models/WithdrawRequest.php'),
            
            // Routes
            $basePath . '/routes/web.php' => base_path('Modules/Wallets/routes/web.php'),
        ];

        foreach ($filesWithNamespaces as $source => $destination) {
            if (File::exists($source)) {
                File::ensureDirectoryExists(dirname($destination));
                
                $content = File::get($source);
                $content = $this->transformNamespaces($content, $source);
                
                File::put($destination, $content);
                $this->info("Published: " . basename($destination));
            } else {
                $this->warn("Source file not found: " . $source);
            }
        }
    }

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
        ];

        // Apply transformations
        foreach ($namespaceTransforms as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        // Handle specific file types
        if (str_contains($sourceFile, 'Controllers')) {
            $content = str_replace('use admin\\wallets\\Models\\Wallet;', 'use Modules\\Wallets\\app\\Models\\Wallet;', $content);
            $content = str_replace('use admin\\wallets\\Models\\WalletTransaction;', 'use Modules\\Wallets\\app\\Models\\WalletTransaction;', $content);
            $content = str_replace('use admin\\wallets\\Models\\WithdrawRequest;', 'use Modules\\Wallets\\app\\Models\\WithdrawRequest;', $content);
        }

        return $content;
    }

    protected function updateComposerAutoload()
    {
        $composerFile = base_path('composer.json');
        $composer = json_decode(File::get($composerFile), true);

        // Add module namespace to autoload
        if (!isset($composer['autoload']['psr-4']['Modules\\Wallets\\'])) {
            $composer['autoload']['psr-4']['Modules\\Wallets\\'] = 'Modules/Wallets/app/';
            
            File::put($composerFile, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->info('Updated composer.json autoload');
        }
    }
}
