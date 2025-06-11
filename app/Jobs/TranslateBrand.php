<?php

namespace App\Jobs;

use App\Models\Brand;
use App\Models\BrandTranslation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Stichoza\GoogleTranslate\GoogleTranslate;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class TranslateBrand implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Brand $brand;
    protected string $originalLang;

    /**
     * Create a new job instance.
     */
    public function __construct(Brand $brand)
    {
        $this->brand = $brand;
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
            name: $this->brand->name,
            description: $this->brand->description ?? ''
        );

        // Step 2: Translate to other languages
        foreach ($allLanguages as $lang) {
            if ($lang === $userLang) continue;

            try {
                $translator = new GoogleTranslate($lang);

                $translatedName = $translator->translate($this->brand->name);
                $translatedDescription = $translator->translate($this->brand->description ?? '');

                $this->storeTranslation(
                    lang: $lang,
                    name: $translatedName,
                    description: $translatedDescription
                );
            } catch (\Throwable $e) {
                Log::error("Brand ID {$this->brand->id} [$userLang ➝ $lang] translation failed: " . $e->getMessage());
            }
        }
    }

    /**
     * Save brand translation for a given language.
     */
    protected function storeTranslation(string $lang, string $name, string $description): void
    {
        BrandTranslation::updateOrCreate(
            ['brand_id' => $this->brand->id, 'lang' => $lang],
            [
                'name' => $name,
                'description' => $description,
                'user_id' => $this->brand->user_id,
            ]
        );
    }
}
