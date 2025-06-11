<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RegisterAdminMiddleware extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'middleware:register-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register the admin middleware in the application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Registering admin middleware...');

        // Check if the middleware is already registered
        $kernelPath = base_path('app/Http/Kernel.php');
        
        if (!File::exists($kernelPath)) {
            $this->error('Kernel.php file not found!');
            return 1;
        }

        $kernelContent = File::get($kernelPath);
        
        // Check if the middleware is already registered
        if (strpos($kernelContent, 'AdminMiddleware::class') !== false) {
            $this->info('Admin middleware is already registered!');
            return 0;
        }

        // Add the middleware to the $routeMiddleware array
        $kernelContent = str_replace(
            "'auth' => \App\Http\Middleware\Authenticate::class,",
            "'auth' => \App\Http\Middleware\Authenticate::class,\n        'admin' => \App\Http\Middleware\AdminMiddleware::class,",
            $kernelContent
        );

        // Save the changes
        File::put($kernelPath, $kernelContent);

        $this->info('Admin middleware registered successfully!');
        return 0;
    }
} 