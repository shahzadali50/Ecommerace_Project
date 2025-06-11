<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class IndexProject extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run all indexing tasks for the project';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting project indexing...');

        // Register the admin middleware
        $this->info('Registering admin middleware...');
        Artisan::call('middleware:register-admin');
        $this->info(Artisan::output());

        // Generate the sitemap
        $this->info('Generating sitemap...');
        Artisan::call('sitemap:generate');
        $this->info(Artisan::output());

        // Clear the cache
        $this->info('Clearing cache...');
        Artisan::call('optimize:clear');
        $this->info(Artisan::output());

        // Optimize the application
        $this->info('Optimizing application...');
        Artisan::call('optimize');
        $this->info(Artisan::output());

        $this->info('Project indexing completed successfully!');
        $this->info('Your project is now ready for search engines to index.');
        $this->info('You can access the sitemap at: ' . url('sitemap.xml'));
        
        return 0;
    }
} 