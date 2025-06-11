<?php

use Illuminate\Support\Facades\Http;

if (!function_exists('autoTranslate')) {
    function autoTranslate($text, $targetLang = 'es')
    {
        try {
            $apiKey = env('GOOGLE_TRANSLATE_API_KEY');
            $url = 'https://translation.googleapis.com/language/translate/v2';

            $response = Http::post($url, [
                'q' => $text,
                'target' => $targetLang,
                'format' => 'text',
                'key' => $apiKey,
            ]);

            $data = $response->json();
            return $data['data']['translations'][0]['translatedText'] ?? $text;
        } catch (\Exception $e) {
            \Log::error("Translation failed: " . $e->getMessage());
            return $text;
        }
    }
}
