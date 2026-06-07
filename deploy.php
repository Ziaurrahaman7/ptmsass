<?php

/**
 * Deployment Script for Production
 * Access: https://yourdomain.com/deploy.php?token=YOUR_SECRET_TOKEN
 * 
 * Setup: 
 * 1. Change the SECRET_TOKEN below
 * 2. Upload this file to your server root
 * 3. Configure git credentials on server (if needed)
 */

define('SECRET_TOKEN', 'change-this-to-a-random-secret-token-123456');

// Security check
if (!isset($_GET['token']) || $_GET['token'] !== SECRET_TOKEN) {
    http_response_code(403);
    die('Unauthorized access');
}

// Set maximum execution time
set_time_limit(300); // 5 minutes

// Output buffer for real-time logs
if (ob_get_level()) ob_end_clean();
header('Content-Type: text/plain; charset=utf-8');

function runCommand($command, $description) {
    echo "\n🔹 {$description}\n";
    echo "Command: {$command}\n";
    
    $output = [];
    $returnVar = 0;
    exec($command . ' 2>&1', $output, $returnVar);
    
    foreach ($output as $line) {
        echo "   {$line}\n";
    }
    
    if ($returnVar !== 0) {
        echo "   ⚠️  Warning: Command returned non-zero exit code: {$returnVar}\n";
    } else {
        echo "   ✅ Success\n";
    }
    
    flush();
    return $returnVar === 0;
}

echo "═══════════════════════════════════════════════════\n";
echo "🚀 DEPLOYMENT STARTED\n";
echo "═══════════════════════════════════════════════════\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";
echo "═══════════════════════════════════════════════════\n";

// Enable maintenance mode
runCommand('php artisan down --retry=60', 'Enabling maintenance mode');

// Pull latest changes (if git is configured)
runCommand('git pull origin main', 'Pulling latest changes from git');

// Install/Update composer dependencies
runCommand('composer install --no-dev --optimize-autoloader --no-interaction', 'Installing composer dependencies');

// Clear all caches
runCommand('php artisan optimize:clear', 'Clearing all caches');

// Run migrations
runCommand('php artisan migrate --force', 'Running database migrations');

// Create storage link
runCommand('php artisan storage:link', 'Creating storage symlink');

// Cache configurations for better performance
runCommand('php artisan config:cache', 'Caching configuration');
runCommand('php artisan route:cache', 'Caching routes');
runCommand('php artisan view:cache', 'Caching views');

// Optimize application
runCommand('php artisan optimize', 'Optimizing application');

// Disable maintenance mode
runCommand('php artisan up', 'Disabling maintenance mode');

echo "\n═══════════════════════════════════════════════════\n";
echo "✅ DEPLOYMENT COMPLETED SUCCESSFULLY!\n";
echo "═══════════════════════════════════════════════════\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";
echo "═══════════════════════════════════════════════════\n";
