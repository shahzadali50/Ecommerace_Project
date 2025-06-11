<?php

namespace App\Jobs;

use App\Models\Brand;
use Illuminate\Bus\Queueable;
use App\Models\BrandTranslation;
use App\Models\ProductTranslation;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Stichoza\GoogleTranslate\GoogleTranslate;
use App\Models\Product;

class TranslateProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Product $product;
    protected string $originalLang;

    /**
     * Create a new job instance.
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
        $this->originalLang = session('locale', App::getLocale()); // Detect current language
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $allLanguages = ['en', 'pt', 'ja'];
        $userLang = $this->originalLang;

        // Step 1: Save original as-is
        $this->storeTranslation(
            lang: $userLang,
            name: $this->product->name,
            description: $this->product->description ?? ''
        );

        // Step 2: Translate to other languages
        foreach ($allLanguages as $lang) {
            if ($lang === $userLang) continue;

            try {
                $translator = new GoogleTranslate($lang);

                $translatedName = $translator->translate($this->product->name);
                $translatedDescription = $translator->translate($this->product->description ?? '');

                $this->storeTranslation(
                    lang: $lang,
                    name: $translatedName,
                    description: $translatedDescription
                );
            } catch (\Throwable $e) {
                Log::error("Product ID {$this->product->id} [$userLang ➝ $lang] translation failed: " . $e->getMessage());
            }
        }
    }

    /**
     * Save brand translation for a given language.
     */
    protected function storeTranslation(string $lang, string $name, string $description): void
    {
        ProductTranslation::updateOrCreate(
            ['product_id' => $this->product->id, 'lang' => $lang],
            [
                'name' => $name,
                'description' => $description,
                'user_id' => $this->product->user_id,
            ]
        );
    }
}
