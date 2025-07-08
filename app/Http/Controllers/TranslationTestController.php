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
            'text' => 'required|string',
            'target' => 'required|string|in:en,es,it,fr,de,ar,hi,ur',
        ]);

        $text = $request->input('text');
        $targetLanguage = $request->input('target');

        if ($targetLanguage === 'en' || empty($targetLanguage)) {
            return response()->json(['translated' => $text]);
        }

        try {
            $tr = new GoogleTranslate();
            $tr->setSource('en');
            $tr->setTarget($targetLanguage);
            $translatedText = $tr->translate($text);
            return response()->json(['translated' => $translatedText]);
        } catch (\Exception $e) {
            \Log::error('Translation error: ' . $e->getMessage());
            return response()->json(['translated' => $text], 500);
        }
    }
}
