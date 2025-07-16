<?php

namespace App\Http\Controllers;

use session;
use Inertia\Inertia;
use App\Models\Product;
use Inertia\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        try {
            $cart = session()->get('cart', []);
            $productId = $request->input('id');
            $qty = $request->input('qty', 1);
            $action = $request->input('action', 'add');
            $locale = session('locale', App::getLocale());

            $product = Product::with(['product_translations' => function($q) use ($locale) {
                $q->where('lang', $locale);
            }])->findOrFail($productId);

            // Check if product has sufficient stock
            if ($product->stock < 1) {
                return back()->with('error', 'Product is out of stock.');
            }

            $exists = false;

            foreach ($cart as &$item) {
                if ($item['id'] === $productId) {
                    if ($action === 'add') {
                        // Check if adding quantity exceeds stock
                        if (($item['qty'] + $qty) > $product->stock) {
                            return back()->with('error', 'Requested quantity exceeds available stock.');
                        }
                        $item['qty'] += $qty;
                    } elseif ($action === 'decrease' && $item['qty'] > 1) {
                        $item['qty'] -= $qty;
                    }

                    $item['thumnail_img'] = $product->thumnail_img;
                    $item['name'] = $product->name;
                    $item['purchase_price'] = $product->purchase_price;
                    $item['final_price'] = $product->final_price;
                    $item['discount'] = $product->discount;
                    $item['total_price'] = $item['qty'] * $product->final_price;
                    $exists = true;
                    break;
                }
            }

            if (!$exists && $action === 'add') {
                // Check if requested quantity exceeds stock
                if ($qty > $product->stock) {
                    return back()->with('error', 'Requested quantity exceeds available stock.');
                }

                $cart[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'thumnail_img' => $product->thumnail_img,
                    'purchase_price' => $product->purchase_price,
                    'final_price' => $product->final_price,
                    'discount' => $product->discount,
                    'qty' => $qty,
                    'total_price' => $qty * $product->final_price,
                ];
            }

            session(['cart' => $cart]);

            return back()->with('success', 'Item added to cart successfully.');
        } catch (\Exception $e) {
            Log::error('Cart update failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to update cart.');
        }
    }

    public function removeFromCart(Request $request)
    {
        try {
            // Get cart from session and ensure it's an array
            $cart = session()->get('cart', []);
            if (!is_array($cart)) {
                $cart = [];
            }

            $productId = (int)$request->input('id');

            // Find the exact item to remove
            $itemToRemove = null;
            foreach ($cart as $key => $item) {
                if ((int)$item['id'] === $productId) {
                    $itemToRemove = $key;
                    break;
                }
            }

            // If item found, remove only that specific item
            if ($itemToRemove !== null) {
                // Remove the specific item
                unset($cart[$itemToRemove]);

                // Re-index the array
                $cart = array_values($cart);

                // Update the session with the new cart
                session()->put('cart', $cart);
                session()->save(); // Force save the session

                // Calculate new totals
                $total = 0;
                foreach ($cart as $item) {
                    $total += $item['total_price'];
                }

                return back()->with('success', 'Cart Item Removed Successfully.');
            }

            Log::warning('Item not found in cart: ' . $productId);

            return back()->with('error', 'Item not found in cart.');
        } catch (\Exception $e) {
            Log::error('Remove from cart failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to remove item from cart.');
        }
    }
public function addToWhishlist(Request $request)
{
    try {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

       $user = auth()->user();
        if (!$user) {
            return back()->with('error', 'Please log in to manage your wishlist.');
        }

        $productId = (int) $request->input('product_id');

        // Check if already in wishlist
        $existing = Wishlist::where('user_id', $user->id)
                            ->where('product_id', $productId)
                            ->first();

        if ($existing) {
            $existing->delete();
            return back()->with('success', 'Product removed from wishlist.');
        } else {
            // Add to wishlist
            Wishlist::create([
                'user_id' => $user->id,
                'product_id' => $productId,
            ]);

            return back()->with('success', 'Product added to wishlist.');
        }
    } catch (\Exception $e) {
        \Log::error('Wishlist toggle failed: ' . $e->getMessage());
        return back()->with('error', 'Something went wrong. Try again.');
    }
}


    public function cartCheckout()
    {

        // session()->flash('success', 'Welcome to checkout page');

        return Inertia::render('frontend/cart/CartCheckout', [
            'locale' => App::getLocale(),
            // 'flash' => session()->only(['success']),
        ]);
    }
    public function cartPayment()
    {
        if (!Auth::check()) {
            return back()->with('error', 'Please login to access checkout.');
        }
        // session()->flash('success', 'Welcome to checkout page');

        return Inertia::render('frontend/cart/CartPayment', [
            'locale' => App::getLocale(),
            'stripe_key' => config('services.stripe.key'),
            // 'flash' => session()->only(['success']),
        ]);
    }
public function wishlist(Request $request)
{
    try {
        $locale = session('locale', App::getLocale());
        $page = $request->query('page', 1);

        if (Auth::check()) {
            // User logged in — eager load wishlist products with category
            $query = Auth::user()->wishlist()
                ->with([
                    'product' => function ($q) {
                        $q->where('status', 1)->with('category');
                    }
                ]);

            $wishlistProducts = $query->paginate(10, ['*'], 'page', $page);

            // Transform for frontend
            $wishlistProducts->getCollection()->transform(function ($wishlistItem) {
                $product = $wishlistItem->product;
                if (!$product) {
                    return null;
                }
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'thumnail_img' => $product->thumnail_img,
                    'sale_price' => (float) $product->sale_price,
                    'final_price' => (float) $product->final_price,
                    'category_name' => $product->category?->name ?? 'N/A',
                ];
            })->filter()->values();

            $wishlist = Auth::user()->wishlist()->pluck('product_id')->toArray();

        } else {
            // Guest user — empty paginated data
            $wishlistProducts = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
            $wishlist = [];
        }


        return Inertia::render('frontend/WishlistProduct', [
            'products' => $wishlistProducts,
            'wishlist' => $wishlist,
            'locale' => $locale,
        ]);
    } catch (\Throwable $e) {
        \Log::error('Failed to load wishlist products in wishlist(): ' . $e->getMessage());
        return redirect()->back()->with('error', 'Something went wrong while loading wishlist products.');
    }
}


}
