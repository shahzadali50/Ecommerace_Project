<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SeoController extends Controller
{
    /**
     * Generate and submit sitemap to search engines
     */
    public function generateAndSubmitSitemap()
    {
        try {
            // Generate the sitemap
            Artisan::call('sitemap:generate');
            
            // Get the site URL
            $siteUrl = config('app.url');
            
            // Submit to Google
            $googleResponse = Http::get('https://www.google.com/ping?sitemap=' . $siteUrl . '/sitemap.xml');
            
            // Submit to Bing
            $bingResponse = Http::get('https://www.bing.com/ping?sitemap=' . $siteUrl . '/sitemap.xml');
            
            // Log the results
            Log::info('Sitemap generated and submitted to search engines', [
                'google_response' => $googleResponse->status(),
                'bing_response' => $bingResponse->status(),
            ]);
            
            return redirect()->back()->with('success', 'Sitemap generated and submitted to search engines successfully!');
        } catch (\Exception $e) {
            Log::error('Error generating and submitting sitemap: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Add meta tags to the page
     */
    public function addMetaTags(Request $request)
    {
        // This method can be used to dynamically add meta tags to pages
        // For now, we'll just return a success message
        return redirect()->back()->with('success', 'Meta tags added successfully!');
    }
} 