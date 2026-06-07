<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class DeployCommand extends Command
{
    protected $signature = 'app:deploy {--skip-git : Skip git pull}';
    protected $description = 'Deploy the application';

    public function handle()
    {
        $this->info('🚀 Starting deployment...');
        
        // Git pull
        if (!$this->option('skip-git')) {
            $this->info('📦 Pulling latest changes...');
            exec('git pull origin main 2>&1', $output, $returnVar);
            if ($returnVar !== 0) {
                $this->warn('Git pull failed or not configured');
            }
        }
        
        // Composer install
        $this->info('📚 Installing dependencies...');
        exec('composer install --no-dev --optimize-autoloader --no-interaction 2>&1', $output, $returnVar);
        
        // Clear caches
        $this->info('🔄 Clearing caches...');
        Artisan::call('optimize:clear');
        
        // Run migrations
        $this->info('📊 Running migrations...');
        Artisan::call('migrate', ['--force' => true]);
        
        // Storage link
        $this->info('🔗 Creating storage link...');
        try {
            Artisan::call('storage:link');
        } catch (\Exception $e) {
            $this->warn('Storage link already exists or failed');
        }
        
        // Cache for production
        $this->info('⚡ Caching for production...');
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');
        Artisan::call('optimize');
        
        $this->info('✅ Deployment completed!');
        
        return 0;
    }
}
