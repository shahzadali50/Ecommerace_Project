<?php

namespace App\Http\Controllers;

use Log;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Brand;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use App\Http\Requests\Auth\LoginRequest;

class MainController extends Controller
{

    public function index()
    {
        try {
            $locale = session()->get('locale');
            app()->setLocale($locale);

            // Load categories with product count
            $categories = Category::withCount('products')
                ->get()
                ->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'image' => $category->image,
                        'slug' => $category->slug,
                        'product_count' => $category->products_count,
                    ];
                });


            // Load products with brand, category, and their translations for current locale
            $products = Product::where('status', 1)
                ->with(['category'])
                ->orderBy('created_at', 'desc')
                ->paginate(8);

            // Transform products to include translated fields
            $products->getCollection()->transform(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'stock' => $product->stock,
                    'thumnail_img' => $product->thumnail_img,
                    'sale_price' => $product->sale_price,
                    'final_price' => $product->final_price,
                    'category_name' => $product->category?->name ?? 'N/A',
                ];
            });
              // ✅ Get wishlist product IDs if user is logged in
                $wishlist = [];
                if (Auth::check()) {
                    $wishlist = Auth::user()->wishlist()->pluck('product_id')->toArray();
                }
                        return Inertia::render('frontend/Index', [
                'categories' => $categories,
                'products' => $products,
                'wishlist' => $wishlist,
                'locale' => $locale,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to load products in index(): ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong while loading products.');
        }
    }
    public function productDetail($slug)
    {
        try {
            $locale = session('locale', App::getLocale());

            $product = Product::where('slug', $slug)
                ->with(['category'])
                ->first();

            if (!$product) {
                return redirect()->back()->with('error', 'Product detail not found.');
            }

            // Get wishlist product IDs if user is logged in
            $wishlist = [];
            if (Auth::check()) {
                $wishlist = Auth::user()->wishlist()->pluck('product_id')->toArray();
            }
               // Get related products (same category, excluding this product)
                $relatedProducts = Product::where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->latest()
                ->take(5) // limit to 4, you can change
                ->get()
                ->map(function ($related) {
                    return [
                        'id' => $related->id,
                        'name' => $related->name,
                        'slug' => $related->slug,
                        'category_name' => $related->category?->name ?? 'N/A',
                        'sale_price' => $related->sale_price,
                        'final_price' => $related->final_price,
                        'stock' => $related->stock,
                        'thumbnail_image' => $related->thumnail_img ? asset("storage/{$related->thumnail_img}") : null,
                    ];
                });

            return Inertia::render('frontend/products/ProductDetail', [
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'slug' => $product->slug,
                    'final_price' => $product->final_price,
                    'sale_price' => $product->sale_price,
                    'discount' => $product->discount,
                    'stock' => $product->stock,
                    'thumbnail_image' => $product->thumnail_img ?? null,
                    'gallery_images' => collect(
                        is_array($product->gallary_img)
                            ? $product->gallary_img
                            : (is_string($product->gallary_img) && str_starts_with($product->gallary_img, '[')
                                ? json_decode($product->gallary_img, true)
                                : explode(',', $product->gallary_img)
                            )
                    )->map(fn($img) => asset("storage/" . trim($img)))->toArray(),
                    'category_name' => $product->category?->name ?? 'N/A',
                ],
                'relatedProducts' => $relatedProducts,
                'wishlist' => $wishlist,
                'locale' => $locale,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Product detail error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong.');
        }
    }



    public function switchLanguage($locale)
    {
        if (in_array($locale, ['en', 'pt', 'ja'])) {
            session(['locale' => $locale]);
        }
        return redirect()->back();
    }

    public function cacheClear()
    {
        try {
            // Run the Artisan command to clear caches
            Artisan::call('optimize:clear');
            // Return success message using Inertia flash data
            Log::info('Application cache cleared successfully!');
            return redirect()->back()->with('success', 'Application cache cleared successfully!');
        } catch (\Exception $e) {
            Log::error('Cache clear error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    public function migrate()
    {
        try {
            // Run the Artisan command to clear caches
            Artisan::call('migrate');
            // Return success message using Inertia flash data
            Log::info('Database migrated successfully.');
            return redirect()->back()->with('success', 'Database migrated successfully.');
        } catch (\Exception $e) {
            Log::error('Migration error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Migration failed: ' . $e->getMessage());
        }
    }
    public function storageLink()
    {
        try {
            Artisan::call('storage:link');
            Log::info('Storage link created successfully.');
            return redirect()->back()->with('success', 'Storage link created successfully.');
        } catch (\Exception $e) {
            Log::error('Storage link error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Storage link failed: ' . $e->getMessage());
        }
    }
    public function checkRole()
    {
        if (auth()->check()) {
            if (auth()->user()->role == "admin") {

                return redirect()->route('admin.dashboard')->with('success', 'login successfully');
            } else {

                return redirect()->route('profile.edit')->with('success', 'login successfully');
            }
        } else {

            return redirect()->route('login');
        }
    }
    public function dashboard()
    {
        try {
            $brands = Brand::where('user_id', Auth::id())->count();
            $totalProduct = Product::where('user_id', Auth::id())->count();
            $category = Category::where('user_id', Auth::id())->count();

            // Send all orders instead of filtering them
            $orders = Order::select('total_price', 'created_at')->get();

            return Inertia::render('admin/Dashboard', [
                'brands' => $brands,
                'totalProduct' => $totalProduct,
                'category' => $category,
                'orders' => $orders,
                'locale' => App::getLocale(),
            ]);
        } catch (\Throwable $e) {
            // Optional: log the error for debugging
            Log::error('Dashboard error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            // You can return a fallback view or redirect with error
            return redirect()->back()->withErrors(['message' => 'Something went wrong while loading the dashboard.']);
        }
    }
    public function loginModal(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();
        return back()->with('success', 'login successfully');
    }
public function allProducts(Request $request)
{
    try {
        $locale = session('locale', App::getLocale());
        $page = $request->query('page', 1);
        $categorySlug = $request->query('category');
        $brandSlug = $request->query('brand');
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');

        // Fetch categories with at least one product
        $categories = Category::withCount('products')
            ->whereHas('products', function ($q) {
                $q->where('status', 1);
            })
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'product_count' => $category->products_count,
                ];
            });

        $brands = Brand::withCount('products')
            ->whereHas('products', function ($q) {
                $q->where('status', 1);
            })
            ->get()
            ->map(function ($brand) {
                return [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'slug' => $brand->slug,
                    'product_count' => $brand->products_count,
                ];
            });

        // Fetch products with optional filters
        $query = Product::where('status', 1)
            ->with(['category']);

        // Category filter
        if ($categorySlug) {
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        // Brand filter
        if ($brandSlug) {
            $query->whereHas('brand', function ($q) use ($brandSlug) {
                $q->where('slug', $brandSlug);
            });
        }

        // Min price filter
        if ($minPrice !== null && $minPrice !== '') {
            $query->where('final_price', '>=', $minPrice);
        }

        // Max price filter
        if ($maxPrice !== null && $maxPrice !== '') {
            $query->where('final_price', '<=', $maxPrice);
        }

        $products = $query->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'page', $page);

        // Transform products to include category name
        $products->getCollection()->transform(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'thumnail_img' => $product->thumnail_img,
                'sale_price' => $product->sale_price,
                'final_price' => $product->final_price,
                'stock' => $product->stock,
                'category_name' => $product->category?->name ?? 'N/A',
            ];
        });

        $wishlist = [];
        if (Auth::check()) {
            $wishlist = Auth::user()->wishlist()->pluck('product_id')->toArray();
        }

        return Inertia::render('frontend/products/AllProducts', [
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
            'wishlist' => $wishlist,
            'locale' => $locale,
            'selectedCategory' => $categorySlug,
            'selectedBrand' => $brandSlug,
        ]);
    } catch (\Throwable $e) {
        \Log::error('Failed to load products: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Something went wrong while loading products.');
    }
}
public function getUser(){
    try {
        $locale = App::getLocale();

        // Get all users with role 'user'
        $users = User::where('role', 'user')
            ->select('id', 'name', 'email', 'created_at')
            ->latest()
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at->format('Y-m-d H:i'),
                ];
            });
        return Inertia::render('admin/user/Index', [
            'users' => [
                'data' => $users // Wrap the collection in a data key
            ],
            'translations' => __('messages'),
            'locale' => $locale,
        ]);

    } catch (\Throwable $e) {
        \Log::error('Failed to load users in getUser(): ' . $e->getMessage());
        return redirect()->back()->with('error', 'Something went wrong while loading users.');
    }
}
}
