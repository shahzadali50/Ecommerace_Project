<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Order;
use App\Models\Product;
use App\Models\SaleProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request; // ✅ Correct import
use Illuminate\Support\Facades\App;
use App\Jobs\OrderTranslationJob;

class OrderController extends Controller
{


public function orderGenerate(Request $request)
{
    $validatedData = $request->validate([
        'payment_method' => 'required|string',
    ]);

    // Retrieve billing details from session
    $billingDetail = session('billingDetail', []);

    // Check if billing details exist in session
    if (empty($billingDetail)) {
        return back()->with('error', 'Billing details are missing. Please provide billing information.');
    }

    // Validate that all required billing details are present
    $requiredFields = [
        'name' => 'string',
        'phone_number' => 'numeric',
        'email' => 'email',
        'address' => 'string',
        'country' => 'string',
        'state' => 'string',
        'city' => 'string',
        'postal_code' => 'numeric',
    ];

    foreach ($requiredFields as $field => $type) {
        if (!isset($billingDetail[$field]) || empty($billingDetail[$field])) {
            return back()->with('error', "Billing detail '$field' is missing or invalid.");
        }
    }

    $cart = session('cart', []);

    if (empty($cart)) {
        return back()->with('error', 'Cart is empty.');
    }

    try {
        DB::beginTransaction();

        $subTotal = collect($cart)->sum(fn($product) => $product['final_price'] * $product['qty']);
        $discount = 0; // You can set dynamic discount logic
        $discountAmount = ($discount / 100) * $subTotal;
        $totalPrice = $subTotal - $discountAmount;

        // Generate a random order ID with prefix ORD-
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
            'payment_method' => $validatedData['payment_method'],
            'order_notes' => $billingDetail['order_notes'] ?? null,
            'user_id' => Auth::id(),
            'subtotal_price' => $subTotal,
            'discount' => $discount,
            'total_price' => $totalPrice,
            'order_id' => $randomOrderId,
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

            // Update product stock
            Product::where('id', $product['id'])->decrement('stock', $product['qty']);
        }

        // Dispatch translation job
        OrderTranslationJob::dispatch($order);

        DB::commit();
        // Clear both cart and billingDetail sessions
        session()->forget(['cart', 'billingDetail']);
        return redirect()->route('user.order.list')->with('success', 'Your order has been placed successfully! Order ID: ' . $randomOrderId);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Order creation failed: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Something went wrong! Please try again.');
    }
}


    public function orderList()
    {
        try {
            $locale = session('locale', App::getLocale());

            // Eager load relationships
            $orders = Order::with([
                'saleProducts.product',
                'saleProducts.product.product_translations' => fn($q) => $q->where('lang', $locale)
            ])
            ->latest()
            ->get();

            // Transform orders data
            $orders = $orders->map(function ($order) use ($locale) {
                return [
                    'id' => $order->id,
                    'order_id' => $order->order_id,
                    'status' => $order->status,
                    'name' => $order->name,
                    'email' => $order->email,
                    'phone_number' => $order->phone_number,
                    'address' => $order->address,
                    'city' => $order->city,
                    'state' => $order->state,
                    'postal_code' => $order->postal_code,
                    'country' => $order->country,
                    'order_notes' => $order->order_notes,
                    'subtotal_price' => $order->subtotal_price,
                    'total_price' => $order->total_price,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'sale_products' => $order->saleProducts->map(function ($saleProduct) use ($locale) {
                        return [
                            'id' => $saleProduct->id,
                            'sale_price' => $saleProduct->sale_price,
                            'qty' => $saleProduct->qty,
                            'total_price' => $saleProduct->total_price,
                            'product' => [
                                'id' => $saleProduct->product->id,
                                'name' => $saleProduct->product->product_translations->first()?->name ?? $saleProduct->product->name,
                                'thumnail_img' => $saleProduct->product->thumnail_img,
                            ]
                        ];
                    })
                ];
            });

            return Inertia::render('admin/order/OrderList', [
                'orders' => [
                    'data' => $orders // Wrap the collection in a data key
                ],
                'translations' => __('messages'),
                'locale' => App::getLocale(),
            ]);

        } catch (\Throwable $e) {
            Log::error('Failed to load orders in orderList(): ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong while loading orders.');
        }
    }
    public function todayOrderList()
    {
        try {
            $locale = session('locale', App::getLocale());

            // Eager load relationships
            $orders = Order::with([
                'saleProducts.product',
                'saleProducts.product.product_translations' => fn($q) => $q->where('lang', $locale)
            ])
               ->whereDate('created_at', now()->toDateString())
            ->latest()
            ->get();

            // Transform orders data
            $orders = $orders->map(function ($order) use ($locale) {
                return [
                    'id' => $order->id,
                    'order_id' => $order->order_id,
                    'status' => $order->status,
                    'name' => $order->name,
                    'email' => $order->email,
                    'phone_number' => $order->phone_number,
                    'address' => $order->address,
                    'city' => $order->city,
                    'state' => $order->state,
                    'postal_code' => $order->postal_code,
                    'country' => $order->country,
                    'order_notes' => $order->order_notes,
                    'subtotal_price' => $order->subtotal_price,
                    'total_price' => $order->total_price,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'sale_products' => $order->saleProducts->map(function ($saleProduct) use ($locale) {
                        return [
                            'id' => $saleProduct->id,
                            'sale_price' => $saleProduct->sale_price,
                            'qty' => $saleProduct->qty,
                            'total_price' => $saleProduct->total_price,
                            'product' => [
                                'id' => $saleProduct->product->id,
                                'name' => $saleProduct->product->product_translations->first()?->name ?? $saleProduct->product->name,
                                'thumnail_img' => $saleProduct->product->thumnail_img,
                            ]
                        ];
                    })
                ];
            });

            return Inertia::render('admin/order/TodayOrderList', [
                'orders' => [
                    'data' => $orders // Wrap the collection in a data key
                ],
                'translations' => __('messages'),
                'locale' => App::getLocale(),
            ]);

        } catch (\Throwable $e) {
            Log::error('Failed to load orders in orderList(): ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong while loading orders.');
        }
    }
    public function LastThreeDaysOrderList()
    {
        try {
            $locale = session('locale', App::getLocale());

            // Eager load relationships
            $orders = Order::with([
                'saleProducts.product',
                'saleProducts.product.product_translations' => fn($q) => $q->where('lang', $locale)
            ])
             ->whereDate('created_at', '>=', now()->subDays(2)->toDateString())
            ->latest()
            ->get();

            // Transform orders data
            $orders = $orders->map(function ($order) use ($locale) {
                return [
                    'id' => $order->id,
                    'order_id' => $order->order_id,
                    'status' => $order->status,
                    'name' => $order->name,
                    'email' => $order->email,
                    'phone_number' => $order->phone_number,
                    'address' => $order->address,
                    'city' => $order->city,
                    'state' => $order->state,
                    'postal_code' => $order->postal_code,
                    'country' => $order->country,
                    'order_notes' => $order->order_notes,
                    'subtotal_price' => $order->subtotal_price,
                    'total_price' => $order->total_price,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'sale_products' => $order->saleProducts->map(function ($saleProduct) use ($locale) {
                        return [
                            'id' => $saleProduct->id,
                            'sale_price' => $saleProduct->sale_price,
                            'qty' => $saleProduct->qty,
                            'total_price' => $saleProduct->total_price,
                            'product' => [
                                'id' => $saleProduct->product->id,
                                'name' => $saleProduct->product->product_translations->first()?->name ?? $saleProduct->product->name,
                                'thumnail_img' => $saleProduct->product->thumnail_img,
                            ]
                        ];
                    })
                ];
            });

            return Inertia::render('admin/order/lastThreeDaysOrderList', [
                'orders' => [
                    'data' => $orders // Wrap the collection in a data key
                ],
                'translations' => __('messages'),
                'locale' => App::getLocale(),
            ]);

        } catch (\Throwable $e) {
            Log::error('Failed to load orders in orderList(): ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong while loading orders.');
        }
    }
    public function lastSevenDaysOrderList()
    {
        try {
            $locale = session('locale', App::getLocale());

            // Eager load relationships
            $orders = Order::with([
                'saleProducts.product',
                'saleProducts.product.product_translations' => fn($q) => $q->where('lang', $locale)
            ])
             ->whereDate('created_at', '>=', now()->subDays(6)->toDateString())
            ->latest()
            ->get();

            // Transform orders data
            $orders = $orders->map(function ($order) use ($locale) {
                return [
                    'id' => $order->id,
                    'order_id' => $order->order_id,
                    'status' => $order->status,
                    'name' => $order->name,
                    'email' => $order->email,
                    'phone_number' => $order->phone_number,
                    'address' => $order->address,
                    'city' => $order->city,
                    'state' => $order->state,
                    'postal_code' => $order->postal_code,
                    'country' => $order->country,
                    'order_notes' => $order->order_notes,
                    'subtotal_price' => $order->subtotal_price,
                    'total_price' => $order->total_price,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'sale_products' => $order->saleProducts->map(function ($saleProduct) use ($locale) {
                        return [
                            'id' => $saleProduct->id,
                            'sale_price' => $saleProduct->sale_price,
                            'qty' => $saleProduct->qty,
                            'total_price' => $saleProduct->total_price,
                            'product' => [
                                'id' => $saleProduct->product->id,
                                'name' => $saleProduct->product->product_translations->first()?->name ?? $saleProduct->product->name,
                                'thumnail_img' => $saleProduct->product->thumnail_img,
                            ]
                        ];
                    })
                ];
            });

            return Inertia::render('admin/order/lastSevenDaysOrderList', [
                'orders' => [
                    'data' => $orders // Wrap the collection in a data key
                ],
                'translations' => __('messages'),
                'locale' => App::getLocale(),
            ]);

        } catch (\Throwable $e) {
            Log::error('Failed to load orders in orderList(): ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong while loading orders.');
        }
    }
    public function lastThirtyDaysOrderList()
    {
        try {
            $locale = session('locale', App::getLocale());

            // Eager load relationships
            $orders = Order::with([
                'saleProducts.product',
                'saleProducts.product.product_translations' => fn($q) => $q->where('lang', $locale)
            ])
         ->whereDate('created_at', '>=', now()->subDays(29)->toDateString()) // last 30 days including today
            ->latest()
            ->get();

            // Transform orders data
            $orders = $orders->map(function ($order) use ($locale) {
                return [
                    'id' => $order->id,
                    'order_id' => $order->order_id,
                    'status' => $order->status,
                    'name' => $order->name,
                    'email' => $order->email,
                    'phone_number' => $order->phone_number,
                    'address' => $order->address,
                    'city' => $order->city,
                    'state' => $order->state,
                    'postal_code' => $order->postal_code,
                    'country' => $order->country,
                    'order_notes' => $order->order_notes,
                    'subtotal_price' => $order->subtotal_price,
                    'total_price' => $order->total_price,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'sale_products' => $order->saleProducts->map(function ($saleProduct) use ($locale) {
                        return [
                            'id' => $saleProduct->id,
                            'sale_price' => $saleProduct->sale_price,
                            'qty' => $saleProduct->qty,
                            'total_price' => $saleProduct->total_price,
                            'product' => [
                                'id' => $saleProduct->product->id,
                                'name' => $saleProduct->product->product_translations->first()?->name ?? $saleProduct->product->name,
                                'thumnail_img' => $saleProduct->product->thumnail_img,
                            ]
                        ];
                    })
                ];
            });

            return Inertia::render('admin/order/ThirtyDaysOrderList', [
                'orders' => [
                    'data' => $orders // Wrap the collection in a data key
                ],
                'translations' => __('messages'),
                'locale' => App::getLocale(),
            ]);

        } catch (\Throwable $e) {
            Log::error('Failed to load orders in orderList(): ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong while loading orders.');
        }
    }
    public function thisMonthOrderList()
    {
        try {
            $locale = session('locale', App::getLocale());

            // Eager load relationships
            $orders = Order::with([
                'saleProducts.product',
                'saleProducts.product.product_translations' => fn($q) => $q->where('lang', $locale)
            ])
           ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->latest()
            ->get();

            // Transform orders data
            $orders = $orders->map(function ($order) use ($locale) {
                return [
                    'id' => $order->id,
                    'order_id' => $order->order_id,
                    'status' => $order->status,
                    'name' => $order->name,
                    'email' => $order->email,
                    'phone_number' => $order->phone_number,
                    'address' => $order->address,
                    'city' => $order->city,
                    'state' => $order->state,
                    'postal_code' => $order->postal_code,
                    'country' => $order->country,
                    'order_notes' => $order->order_notes,
                    'subtotal_price' => $order->subtotal_price,
                    'total_price' => $order->total_price,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'sale_products' => $order->saleProducts->map(function ($saleProduct) use ($locale) {
                        return [
                            'id' => $saleProduct->id,
                            'sale_price' => $saleProduct->sale_price,
                            'qty' => $saleProduct->qty,
                            'total_price' => $saleProduct->total_price,
                            'product' => [
                                'id' => $saleProduct->product->id,
                                'name' => $saleProduct->product->product_translations->first()?->name ?? $saleProduct->product->name,
                                'thumnail_img' => $saleProduct->product->thumnail_img,
                            ]
                        ];
                    })
                ];
            });

            return Inertia::render('admin/order/ThisMonthOrderList', [
                'orders' => [
                    'data' => $orders // Wrap the collection in a data key
                ],
                'translations' => __('messages'),
                'locale' => App::getLocale(),
            ]);

        } catch (\Throwable $e) {
            Log::error('Failed to load orders in orderList(): ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong while loading orders.');
        }
    }
    public function pendingOrderList()
    {
        try {
            $locale = session('locale', App::getLocale());

            // Eager load relationships
            $orders = Order::with([
                'saleProducts.product',
                'saleProducts.product.product_translations' => fn($q) => $q->where('lang', $locale)
            ])
              ->where('status', 'pending')
            ->latest()
            ->get();

            // Transform orders data
            $orders = $orders->map(function ($order) use ($locale) {
                return [
                    'id' => $order->id,
                    'order_id' => $order->order_id,
                    'status' => $order->status,
                    'name' => $order->name,
                    'email' => $order->email,
                    'phone_number' => $order->phone_number,
                    'address' => $order->address,
                    'city' => $order->city,
                    'state' => $order->state,
                    'postal_code' => $order->postal_code,
                    'country' => $order->country,
                    'order_notes' => $order->order_notes,
                    'subtotal_price' => $order->subtotal_price,
                    'total_price' => $order->total_price,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'sale_products' => $order->saleProducts->map(function ($saleProduct) use ($locale) {
                        return [
                            'id' => $saleProduct->id,
                            'sale_price' => $saleProduct->sale_price,
                            'qty' => $saleProduct->qty,
                            'total_price' => $saleProduct->total_price,
                            'product' => [
                                'id' => $saleProduct->product->id,
                                'name' => $saleProduct->product->product_translations->first()?->name ?? $saleProduct->product->name,
                                'thumnail_img' => $saleProduct->product->thumnail_img,
                            ]
                        ];
                    })
                ];
            });

            return Inertia::render('admin/order/PendingOrder', [
                'orders' => [
                    'data' => $orders // Wrap the collection in a data key
                ],
                'translations' => __('messages'),
                'locale' => App::getLocale(),
            ]);

        } catch (\Throwable $e) {
            Log::error('Failed to load orders in orderList(): ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong while loading orders.');
        }
    }
    public function processingOrderList()
    {
        try {
            $locale = session('locale', App::getLocale());

            // Eager load relationships
            $orders = Order::with([
                'saleProducts.product',
                'saleProducts.product.product_translations' => fn($q) => $q->where('lang', $locale)
            ])
              ->where('status', 'processing')
            ->latest()
            ->get();

            // Transform orders data
            $orders = $orders->map(function ($order) use ($locale) {
                return [
                    'id' => $order->id,
                    'order_id' => $order->order_id,
                    'status' => $order->status,
                    'name' => $order->name,
                    'email' => $order->email,
                    'phone_number' => $order->phone_number,
                    'address' => $order->address,
                    'city' => $order->city,
                    'state' => $order->state,
                    'postal_code' => $order->postal_code,
                    'country' => $order->country,
                    'order_notes' => $order->order_notes,
                    'subtotal_price' => $order->subtotal_price,
                    'total_price' => $order->total_price,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'sale_products' => $order->saleProducts->map(function ($saleProduct) use ($locale) {
                        return [
                            'id' => $saleProduct->id,
                            'sale_price' => $saleProduct->sale_price,
                            'qty' => $saleProduct->qty,
                            'total_price' => $saleProduct->total_price,
                            'product' => [
                                'id' => $saleProduct->product->id,
                                'name' => $saleProduct->product->product_translations->first()?->name ?? $saleProduct->product->name,
                                'thumnail_img' => $saleProduct->product->thumnail_img,
                            ]
                        ];
                    })
                ];
            });

            return Inertia::render('admin/order/ProcessingOrder', [
                'orders' => [
                    'data' => $orders // Wrap the collection in a data key
                ],
                'translations' => __('messages'),
                'locale' => App::getLocale(),
            ]);

        } catch (\Throwable $e) {
            Log::error('Failed to load orders in orderList(): ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong while loading orders.');
        }
    }
    public function dispatchedOrderList()
    {
        try {
            $locale = session('locale', App::getLocale());

            // Eager load relationships
            $orders = Order::with([
                'saleProducts.product',
                'saleProducts.product.product_translations' => fn($q) => $q->where('lang', $locale)
            ])
              ->where('status', 'dispatched')
            ->latest()
            ->get();

            // Transform orders data
            $orders = $orders->map(function ($order) use ($locale) {
                return [
                    'id' => $order->id,
                    'order_id' => $order->order_id,
                    'status' => $order->status,
                    'name' => $order->name,
                    'email' => $order->email,
                    'phone_number' => $order->phone_number,
                    'address' => $order->address,
                    'city' => $order->city,
                    'state' => $order->state,
                    'postal_code' => $order->postal_code,
                    'country' => $order->country,
                    'order_notes' => $order->order_notes,
                    'subtotal_price' => $order->subtotal_price,
                    'total_price' => $order->total_price,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'sale_products' => $order->saleProducts->map(function ($saleProduct) use ($locale) {
                        return [
                            'id' => $saleProduct->id,
                            'sale_price' => $saleProduct->sale_price,
                            'qty' => $saleProduct->qty,
                            'total_price' => $saleProduct->total_price,
                            'product' => [
                                'id' => $saleProduct->product->id,
                                'name' => $saleProduct->product->product_translations->first()?->name ?? $saleProduct->product->name,
                                'thumnail_img' => $saleProduct->product->thumnail_img,
                            ]
                        ];
                    })
                ];
            });

            return Inertia::render('admin/order/DispatchedOrder', [
                'orders' => [
                    'data' => $orders // Wrap the collection in a data key
                ],
                'translations' => __('messages'),
                'locale' => App::getLocale(),
            ]);

        } catch (\Throwable $e) {
            Log::error('Failed to load orders in orderList(): ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong while loading orders.');
        }
    }
    public function deliveredOrderList()
    {
        try {
            $locale = session('locale', App::getLocale());

            // Eager load relationships
            $orders = Order::with([
                'saleProducts.product',
                'saleProducts.product.product_translations' => fn($q) => $q->where('lang', $locale)
            ])
              ->where('status', 'delivered')
            ->latest()
            ->get();

            // Transform orders data
            $orders = $orders->map(function ($order) use ($locale) {
                return [
                    'id' => $order->id,
                    'order_id' => $order->order_id,
                    'status' => $order->status,
                    'name' => $order->name,
                    'email' => $order->email,
                    'phone_number' => $order->phone_number,
                    'address' => $order->address,
                    'city' => $order->city,
                    'state' => $order->state,
                    'postal_code' => $order->postal_code,
                    'country' => $order->country,
                    'order_notes' => $order->order_notes,
                    'subtotal_price' => $order->subtotal_price,
                    'total_price' => $order->total_price,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'sale_products' => $order->saleProducts->map(function ($saleProduct) use ($locale) {
                        return [
                            'id' => $saleProduct->id,
                            'sale_price' => $saleProduct->sale_price,
                            'qty' => $saleProduct->qty,
                            'total_price' => $saleProduct->total_price,
                            'product' => [
                                'id' => $saleProduct->product->id,
                                'name' => $saleProduct->product->product_translations->first()?->name ?? $saleProduct->product->name,
                                'thumnail_img' => $saleProduct->product->thumnail_img,
                            ]
                        ];
                    })
                ];
            });

            return Inertia::render('admin/order/DeliveredOrder', [
                'orders' => [
                    'data' => $orders // Wrap the collection in a data key
                ],
                'translations' => __('messages'),
                'locale' => App::getLocale(),
            ]);

        } catch (\Throwable $e) {
            Log::error('Failed to load orders in orderList(): ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong while loading orders.');
        }
    }
    public function cancelledOrderList()
    {
        try {
            $locale = session('locale', App::getLocale());

            // Eager load relationships
            $orders = Order::with([
                'saleProducts.product',
                'saleProducts.product.product_translations' => fn($q) => $q->where('lang', $locale)
            ])
              ->where('status', 'cancelled')
            ->latest()
            ->get();

            // Transform orders data
            $orders = $orders->map(function ($order) use ($locale) {
                return [
                    'id' => $order->id,
                    'order_id' => $order->order_id,
                    'status' => $order->status,
                    'name' => $order->name,
                    'email' => $order->email,
                    'phone_number' => $order->phone_number,
                    'address' => $order->address,
                    'city' => $order->city,
                    'state' => $order->state,
                    'postal_code' => $order->postal_code,
                    'country' => $order->country,
                    'order_notes' => $order->order_notes,
                    'subtotal_price' => $order->subtotal_price,
                    'total_price' => $order->total_price,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'sale_products' => $order->saleProducts->map(function ($saleProduct) use ($locale) {
                        return [
                            'id' => $saleProduct->id,
                            'sale_price' => $saleProduct->sale_price,
                            'qty' => $saleProduct->qty,
                            'total_price' => $saleProduct->total_price,
                            'product' => [
                                'id' => $saleProduct->product->id,
                                'name' => $saleProduct->product->product_translations->first()?->name ?? $saleProduct->product->name,
                                'thumnail_img' => $saleProduct->product->thumnail_img,
                            ]
                        ];
                    })
                ];
            });

            return Inertia::render('admin/order/CancelledOrder', [
                'orders' => [
                    'data' => $orders // Wrap the collection in a data key
                ],
                'translations' => __('messages'),
                'locale' => App::getLocale(),
            ]);

        } catch (\Throwable $e) {
            Log::error('Failed to load orders in orderList(): ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong while loading orders.');
        }
    }
    public function returnedOrderList()
    {
        try {
            $locale = session('locale', App::getLocale());

            // Eager load relationships
            $orders = Order::with([
                'saleProducts.product',
                'saleProducts.product.product_translations' => fn($q) => $q->where('lang', $locale)
            ])
              ->where('status', 'returned')
            ->latest()
            ->get();

            // Transform orders data
            $orders = $orders->map(function ($order) use ($locale) {
                return [
                    'id' => $order->id,
                    'order_id' => $order->order_id,
                    'status' => $order->status,
                    'name' => $order->name,
                    'email' => $order->email,
                    'phone_number' => $order->phone_number,
                    'address' => $order->address,
                    'city' => $order->city,
                    'state' => $order->state,
                    'postal_code' => $order->postal_code,
                    'country' => $order->country,
                    'order_notes' => $order->order_notes,
                    'subtotal_price' => $order->subtotal_price,
                    'total_price' => $order->total_price,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'sale_products' => $order->saleProducts->map(function ($saleProduct) use ($locale) {
                        return [
                            'id' => $saleProduct->id,
                            'sale_price' => $saleProduct->sale_price,
                            'qty' => $saleProduct->qty,
                            'total_price' => $saleProduct->total_price,
                            'product' => [
                                'id' => $saleProduct->product->id,
                                'name' => $saleProduct->product->product_translations->first()?->name ?? $saleProduct->product->name,
                                'thumnail_img' => $saleProduct->product->thumnail_img,
                            ]
                        ];
                    })
                ];
            });

            return Inertia::render('admin/order/ReturnedOrder', [
                'orders' => [
                    'data' => $orders // Wrap the collection in a data key
                ],
                'translations' => __('messages'),
                'locale' => App::getLocale(),
            ]);

        } catch (\Throwable $e) {
            Log::error('Failed to load orders in orderList(): ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong while loading orders.');
        }
    }
    public function refundedOrderList()
    {
        try {
            $locale = session('locale', App::getLocale());

            // Eager load relationships
            $orders = Order::with([
                'saleProducts.product',
                'saleProducts.product.product_translations' => fn($q) => $q->where('lang', $locale)
            ])
              ->where('status', 'refunded')
            ->latest()
            ->get();

            // Transform orders data
            $orders = $orders->map(function ($order) use ($locale) {
                return [
                    'id' => $order->id,
                    'order_id' => $order->order_id,
                    'status' => $order->status,
                    'name' => $order->name,
                    'email' => $order->email,
                    'phone_number' => $order->phone_number,
                    'address' => $order->address,
                    'city' => $order->city,
                    'state' => $order->state,
                    'postal_code' => $order->postal_code,
                    'country' => $order->country,
                    'order_notes' => $order->order_notes,
                    'subtotal_price' => $order->subtotal_price,
                    'total_price' => $order->total_price,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'sale_products' => $order->saleProducts->map(function ($saleProduct) use ($locale) {
                        return [
                            'id' => $saleProduct->id,
                            'sale_price' => $saleProduct->sale_price,
                            'qty' => $saleProduct->qty,
                            'total_price' => $saleProduct->total_price,
                            'product' => [
                                'id' => $saleProduct->product->id,
                                'name' => $saleProduct->product->product_translations->first()?->name ?? $saleProduct->product->name,
                                'thumnail_img' => $saleProduct->product->thumnail_img,
                            ]
                        ];
                    })
                ];
            });

            return Inertia::render('admin/order/RefundedOrder', [
                'orders' => [
                    'data' => $orders // Wrap the collection in a data key
                ],
                'translations' => __('messages'),
                'locale' => App::getLocale(),
            ]);

        } catch (\Throwable $e) {
            Log::error('Failed to load orders in orderList(): ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong while loading orders.');
        }
    }
    public function userOrderList()
    {
        try {
            $orders = Order::with('saleProducts.product')
                ->where('user_id', Auth::id())
                ->latest()
                ->get();

            return Inertia::render('frontend/order/Index', [
                'orders' => [
                    'data' => $orders // Wrap the collection in a data key
                ],
                'translations' => __('messages'),
                'locale' => App::getLocale(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to load user orders: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong while loading your orders.');
        }
    }

    public function billingDetail(Request $request)
{
    $validatedData = $request->validate([
        'name' => 'required|string',
        'phone_number' => 'required|numeric',
        'email' => 'required|email',
        'address' => 'required|string',
        'country' => 'required|string',
        'state' => 'required|string',
        'city' => 'required|string',
        'postal_code' => 'required|numeric',
        'order_notes' => 'nullable|string',
    ]);
    try {

          // Check if billingDetail session exists
            if (session()->has('billingDetail')) {
                // Update existing session data
                session()->put('billingDetail', $validatedData);
                // Log::info('Billing details updated in session', ['billingDetail' => $validatedData]);
            } else {
                // Store new session data
                session(['billingDetail' => $validatedData]);
                // Log::info('Billing details stored in session', ['billingDetail' => $validatedData]);
            }
             return redirect()->route('cart.payment')->with('success', 'Billing details stored successfully! ');

    } catch (\Exception $e) {
        Log::error('Failed to store billing details: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Something went wrong while storing billing details. Please try again.');
    }
}

    public function updateOrderStatus(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:pending,processing,dispatched,delivered,cancelled,returned,refunded'
            ]);

            $order = Order::findOrFail($id);
            $order->status = $validated['status'];
            $order->save();

            return redirect()->back()->with('success', 'Order status updated successfully.');

        } catch (\Throwable $e) {
            Log::error('Failed to update order status: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong! Please try again.');
        }
    }

}
