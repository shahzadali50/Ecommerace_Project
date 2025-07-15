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

        if ($targetLanguage === 'en' || empty($targetLanguage)) {
            return response()->json([
                'translations' => collect($texts)->mapWithKeys(fn($t) => [$t => $t])->toArray()
            ]);
        }

        $result = [];
        $toTranslate = [];

        foreach ($texts as $text) {
            $cached = \Cache::get("translation:{$targetLanguage}:" . md5($text));
            if ($cached) {
                $result[$text] = $cached;
            } else {
                $toTranslate[] = $text;
            }
        }

        if (!empty($toTranslate)) {
            try {
                $tr = new GoogleTranslate();
                $tr->setSource('en')->setTarget($targetLanguage);

                foreach ($toTranslate as $text) {
                    $translation = $tr->translate($text);
                    $result[$text] = $translation;
                    \Cache::put("translation:{$targetLanguage}:" . md5($text), $translation, now()->addHours(24));
                }
            } catch (\Exception $e) {
                \Log::error('Translation error: ' . $e->getMessage());
                foreach ($toTranslate as $text) {
                    $result[$text] = $text;
                }
                return response()->json(['translations' => $result], 500);
            }
        }

        return response()->json(['translations' => $result]);
    }
}
