<?php

namespace App\Jobs;

use App\Models\Category;
use App\Models\CategoryTranslation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TranslateCategory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Category $category;
    protected string $originalLang;

    public function __construct(Category $category)
    {
        $this->category = $category;
        $this->originalLang = session('locale', App::getLocale()); // e.g. pt, en, ja
    }

    public function handle(): void
    {
        $allLanguages = ['en', 'pt', 'ja'];
        $userLang = $this->originalLang;

        // 1. Save original (user-submitted) values as-is
        $this->storeTranslation(
            lang: $userLang,
            name: $this->category->name,
            description: $this->category->description ?? ''
        );

        // 2. Translate to the other two languages
        foreach ($allLanguages as $lang) {
            if ($lang === $userLang) continue;

            try {
                $translator = new GoogleTranslate($lang);

                $translatedName = $translator->translate($this->category->name);
                $translatedDescription = $translator->translate($this->category->description ?? '');

                $this->storeTranslation(
                    lang: $lang,
                    name: $translatedName,
                    description: $translatedDescription
                );
            } catch (\Throwable $e) {
                Log::error("Category ID {$this->category->id} [$userLang ➝ $lang] translation failed: " . $e->getMessage());
            }
        }
    }

    protected function storeTranslation(string $lang, string $name, string $description): void
    {
        CategoryTranslation::updateOrCreate(
            [
                'category_id' => $this->category->id,
                'lang' => $lang,
            ],
            [
                'name' => $name,
                'description' => $description,
                'user_id' => $this->category->user_id,
            ]
        );
    }
}
