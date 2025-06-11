<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Carbon\Carbon;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the sitemap for the website';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating sitemap...');

        // Create sitemap content
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        // Add static routes
        $staticRoutes = [
            route('home') => '1.0',
        ];

        foreach ($staticRoutes as $url => $priority) {
            $sitemap .= $this->addUrl($url, $priority);
        }

        // Add dynamic routes for products
        $products = Product::all();
        foreach ($products as $product) {
            $url = route('related-product-list', ['slug' => $product->slug]);
            $sitemap .= $this->addUrl($url, '0.8', $product->updated_at);
        }

        // Add dynamic routes for categories
        $categories = Category::all();
        foreach ($categories as $category) {
            $url = route('related-brand-list', ['slug' => $category->slug]);
            $sitemap .= $this->addUrl($url, '0.7', $category->updated_at);
        }

        // Add dynamic routes for brands
        $brands = Brand::all();
        foreach ($brands as $brand) {
            $url = route('related-brand-list', ['slug' => $brand->slug]);
            $sitemap .= $this->addUrl($url, '0.7', $brand->updated_at);
        }

        $sitemap .= '</urlset>';

        // Save the sitemap
        File::put(public_path('sitemap.xml'), $sitemap);

        $this->info('Sitemap generated successfully!');
    }

    /**
     * Add a URL to the sitemap
     *
     * @param string $url
     * @param string $priority
     * @param Carbon|null $lastmod
     * @return string
     */
    private function addUrl($url, $priority, $lastmod = null)
    {
        $lastmod = $lastmod ? $lastmod->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        
        return '  <url>' . PHP_EOL .
               '    <loc>' . $url . '</loc>' . PHP_EOL .
               '    <lastmod>' . $lastmod . '</lastmod>' . PHP_EOL .
               '    <changefreq>weekly</changefreq>' . PHP_EOL .
               '    <priority>' . $priority . '</priority>' . PHP_EOL .
               '  </url>' . PHP_EOL;
    }
} 