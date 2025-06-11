<?php

namespace App\Http\Controllers;

use Log;
use Stripe\Stripe;
use App\Models\Order;
use App\Models\Product;
use App\Models\SaleProduct;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use App\Jobs\OrderTranslationJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StripeController extends Controller
{

    public function createCheckoutSession(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $cart = session('cart', []);

        if (empty($cart)) {
            return back()->with('error', 'Your Cart is empty.');
        }

        $subTotal = collect($cart)->sum(function ($product) {
            return $product['final_price'] * $product['qty'];
        });

        $discount = 0; // Apply dynamic discount if needed
        $discountAmount = ($discount / 100) * $subTotal;
        $totalPrice = $subTotal - $discountAmount;

        // Stripe requires the amount in cents (or the smallest unit)
        $totalAmountInCents = (int) ($totalPrice * 100);

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'pkr', // or 'usd'
                    'unit_amount' => $totalAmountInCents,
                    'product_data' => [
                        'name' => 'Order from MyStore',
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => url('/payment/success'),
            'cancel_url' => url('/payment/cancel'),
        ]);

        return response()->json(['id' => $session->id]);
    }

    public function paymentSuccess(Request $request)
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $sessionId = $request->query('session_id');

            if (!$sessionId) {
                return redirect()->route('home')->with('error', 'Invalid payment session.');
            }

            $session = \Stripe\Checkout\Session::retrieve($sessionId);

            // Retrieve billing and cart session
            $billingDetail = session('billingDetail', []);
            $cart = session('cart', []);

            if (empty($billingDetail) || empty($cart)) {
                return redirect()->route('home')->with('error', 'Session expired or missing billing/cart data.');
            }

            DB::beginTransaction();

            // Total calculation
            $subTotal = collect($cart)->sum(fn($product) => $product['final_price'] * $product['qty']);
            $discount = 0; // or apply your logic
            $discountAmount = ($discount / 100) * $subTotal;
            $totalPrice = $subTotal - $discountAmount;

            $randomOrderId = 'ORD-' . rand(100000, 999999);

            $order = Order::create([
                'name' => $billingDetail['name'],
                'phone_number' => $billingDetail['phone_number'],
                'email' => $billingDetail['email'],
                'address' => $billingDetail['address'],
                'country' => $billingDetail['country'],
                'state' => $billingDetail['state'],
                'city' => $billingDetail['city'],
                'postal_code' => $billingDetail['postal_code'],
                'payment_method' => 'stripe',
                'order_notes' => $billingDetail['order_notes'] ?? null,
                'user_id' => Auth::id(),
                'subtotal_price' => $subTotal,
                'discount' => $discount,
                'total_price' => $totalPrice,
                'order_id' => $randomOrderId,
                'stripe_session_id' => $session->id,
                'payment_status' => 'paid',
                'status' => 'processing',
            ]);

            foreach ($cart as $product) {
                SaleProduct::create([
                    'order_id' => $order->id,
                    'product_id' => $product['id'],
                    'user_id' => Auth::id(),
                    'sale_price' => $product['final_price'],
                    'qty' => $product['qty'],
                    'total_price' => $product['final_price'] * $product['qty'],
                ]);

                Product::where('id', $product['id'])->decrement('stock', $product['qty']);
            }

            OrderTranslationJob::dispatch($order);

            DB::commit();

            // Clear session data
            session()->forget(['cart', 'billingDetail']);

            return redirect()->route('user.order.list')->with('success', 'Thank you! Your payment was successful and your order has been placed. Order ID: ' . $randomOrderId);
        } catch (\Exception $e) {
            DB::rollBack();
             \Log::error('Stripe Payment Success Error: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Something went wrong while placing your order.');
        }
    }

    public function paymentCancel(){
    return redirect()->route('cart.payment');

    }
}
