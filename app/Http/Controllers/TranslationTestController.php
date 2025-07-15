<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TranslationTestController extends Controller
{
    public function switchLanguage(Request $request)
    {
        $locale = $request->input('language');
        if (!in_array($locale, ['en', 'es', 'it', 'fr', 'de', 'ar', 'hi', 'ur'])) {
            return redirect()->back()->with('error', 'Invalid language selected.');
        }
        session(['locale' => $locale]);
        app()->setLocale($locale);
        return redirect()->back()->with('success', 'Language set to: ' . $locale);
    }

    public function index()
    {
        $locale = session('locale', config('app.locale'));

        return Inertia::render('frontend/TranslationTest', [
            'locale' => $locale,
            'available_languages' => [
                ['code' => 'en', 'name' => 'English'],
                ['code' => 'es', 'name' => 'Spanish'],
                ['code' => 'it', 'name' => 'Italian'],
                ['code' => 'fr', 'name' => 'French'],
                ['code' => 'de', 'name' => 'German'],
                ['code' => 'ar', 'name' => 'Arabic'],
                ['code' => 'hi', 'name' => 'Hindi'],
                ['code' => 'ur', 'name' => 'Urdu'],
            ],
        ]);
    }
    public function translate(Request $request)
    {
        $request->validate([
            'texts' => 'required|array',
            'texts.*' => 'required|string',
            'target' => 'required|string|in:en,es,it,fr,de,ar,hi,ur',
        ]);

        $texts = $request->input('texts');
        $targetLanguage = $request->input('target');

        // If target is English or empty, just return as-is
        if ($targetLanguage === 'en' || empty($targetLanguage)) {
            $result = collect($texts)->mapWithKeys(fn($text) => [$text => $text])->toArray();
            return response()->json(['translations' => $result]);
        }

        try {
            $tr = new \Stichoza\GoogleTranslate\GoogleTranslate();
            $tr->setSource('en');
            $tr->setTarget($targetLanguage);

            $result = [];
            foreach ($texts as $text) {
                $result[$text] = $tr->translate($text);
            }

            return response()->json(['translations' => $result]);
        } catch (\Exception $e) {
            \Log::error('Translation error: ' . $e->getMessage());
            $fallback = collect($texts)->mapWithKeys(fn($text) => [$text => $text])->toArray();
            return response()->json(['translations' => $fallback], 500);
        }
    }

}
