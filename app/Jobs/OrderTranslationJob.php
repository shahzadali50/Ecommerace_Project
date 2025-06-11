<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\OrderTranslation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Stichoza\GoogleTranslate\GoogleTranslate;

class OrderTranslationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Order $order;
    protected string $originalLang;

    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->originalLang = session('locale', App::getLocale());
    }

    public function handle(): void
    {
        $allLanguages = ['en', 'pt', 'ja'];
        $userLang = $this->originalLang;

        // 1. Save original (user-submitted) values as-is
        $this->storeTranslation(
            lang: $userLang,
            name: $this->order->name,
            address: $this->order->address,
            city: $this->order->city,
            state: $this->order->state,
            country: $this->order->country,
            order_notes: $this->order->order_notes,
            payment_method: $this->order->payment_method,
            status: 'pending'
        );

        // 2. Translate to other languages
        foreach ($allLanguages as $lang) {
            if ($lang === $userLang) continue;

            try {
                $translator = new GoogleTranslate($lang);

                $translatedName = $translator->translate($this->order->name);
                $translatedAddress = $translator->translate($this->order->address);
                $translatedCity = $translator->translate($this->order->city);
                $translatedState = $translator->translate($this->order->state);
                $translatedCountry = $translator->translate($this->order->country);
                $translatedOrderNotes = $this->order->order_notes ? $translator->translate($this->order->order_notes) : null;
                $translatedPaymentMethod = $translator->translate($this->order->payment_method);

                $this->storeTranslation(
                    lang: $lang,
                    name: $translatedName,
                    address: $translatedAddress,
                    city: $translatedCity,
                    state: $translatedState,
                    country: $translatedCountry,
                    order_notes: $translatedOrderNotes,
                    payment_method: $translatedPaymentMethod,
                    status: 'pending'
                );
            } catch (\Throwable $e) {
                Log::error("Order ID {$this->order->id} [$userLang ➝ $lang] translation failed: " . $e->getMessage());
            }
        }
    }

    protected function storeTranslation(
        string $lang,
        string $name,
        string $address,
        string $city,
        string $state,
        string $country,
        ?string $order_notes,
        string $payment_method,
        string $status
    ): void {
        OrderTranslation::updateOrCreate(
            [
                'order_id' => $this->order->id,
                'lang' => $lang,
            ],
            [
                'name' => $name,
                'address' => $address,
                'city' => $city,
                'state' => $state,
                'country' => $country,
                'order_notes' => $order_notes,
                'payment_method' => $payment_method,
                'status' => $status,
                'user_id' => $this->order->user_id,
            ]
        );
    }
}
