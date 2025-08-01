<?php

namespace App\Http\Controllers;

use Exception;
use Inertia\Inertia;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductLog;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Jobs\TranslateProduct;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        try {
            // Load products with brand and category relationships
            $products = Product::where('user_id', Auth::id())
                ->with(['brand', 'category'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Transform the collection with basic data
            $products = $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'slug' => $product->slug,
                    'name' => $product->name,
                    'thumnail_img' => $product->thumnail_img,
                    'gallary_img' => $product->gallary_img,
                    'description' => $product->description,
                    'stock' => $product->stock,
                    'purchase_price' => $product->purchase_price,
                    'sale_price' => $product->sale_price,
                    'brand_id' => $product->brand_id,
                    'discount' => $product->discount,
                    'final_price' => $product->final_price,
                    'barcode' => $product->barcode,
                    'feature' => $product->feature,
                    'status' => $product->status,
                    'category_id' => $product->category_id,
                    'created_at' => $product->created_at->format('Y-m-d H:i'),
                    'brand_name' => $product->brand?->name ?? 'N/A',
                    'category_name' => $product->category?->name ?? 'N/A',
                ];
            });

            // Load brands
            $brands = Brand::select('id', 'name')->get();
            // Load categories with hierarchical structure
            $categories = Category::where('user_id', Auth::id())
                ->select('id', 'name', 'parent_id')
                ->with(['children' => function($query) {
                    $query->select('id', 'name', 'parent_id');
                }])
                ->whereNull('parent_id')
                ->get();

            return Inertia::render('admin/product/Index', [
                'products' => ['data' => $products],
                'brands' => ['data' => $brands],
                'categories' => ['data' => $categories],
                'locale' => App::getLocale(),
            ]);

        } catch (\Throwable $e) {
            \Log::error('Failed to load products in index(): ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong while loading products.');
        }
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'name')->whereNull('deleted_at'),
            ],
            'description' => 'required|string',
            'brand_id' => 'required|exists:brands,id',
            'category_id' => 'required|exists:categories,id',
            'thumnail_img' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:1048',
            'gallary_img' => 'required',
            'gallary_img.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5048',
            'stock' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'discount' => 'nullable|integer|min:0|max:100',
            'final_price' => 'required|numeric|min:0',
            'feature' => 'nullable|boolean',
            'barcode' => 'nullable|string|max:255|unique:products,barcode',
        ]);

        DB::beginTransaction();

        try {
            // Store thumbnail image
            $thumbnailPath = $request->file('thumnail_img')->store('products/thumbnails', 'public');

            // Store gallery images
            $galleryPaths = [];
            if ($request->hasFile('gallary_img')) {
                foreach ($request->file('gallary_img') as $image) {
                    $path = $image->store('products/gallery', 'public');
                    $galleryPaths[] = $path;
                }
            }

            // Create product
            $product = Product::create([
                'name' => $request->name,
                'slug' => \Str::slug($request->name),
                'description' => $request->description,
                'brand_id' => $request->brand_id,
                'category_id' => $request->category_id,
                'thumnail_img' => $thumbnailPath,
                'gallary_img' => json_encode($galleryPaths),
                'stock' => $request->stock,
                'status' => $request->status,
                'purchase_price' => $request->purchase_price,
                'sale_price' => $request->sale_price,
                'discount' => $request->discount ?? 0,
                'final_price' => $request->final_price,
                'feature' => $request->feature ?? false,
                'barcode' => $request->barcode,
                'user_id' => Auth::id(),
            ]);  // Create product log
            $user = Auth::user();
            $note = 'Product "' . $product->name . '" created by ' . ($user->name ?? 'Unknown User') .
                    ' with purchase price: ' . $product->purchase_price .
                    ', sale price: ' . $product->sale_price .
                    ', discount: ' . $product->discount . '%' .
                    ', final price: ' . $product->final_price;
            ProductLog::create([
                'note' => $note,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'user_id' => Auth::id(),
            ]);
            DB::commit();
            return redirect()->back()->with('success', 'Product created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product creation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong! Please try again.');
        }
    }

    public function related_product_list($slug)
    {
        $brand = Brand::where('slug', $slug)->first();

        if ($brand) {
            $products = $brand->products()
                ->with(['brand'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return Inertia::render('admin/product/BrandProductList', compact('products', 'brand'));
        } else {
            return redirect()->back()->with('error', 'Record Not Found');
        }
    }
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return redirect()->back()->with('error', 'Product not found.');
        }

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $note = 'Product "' . $product->name . '" Deleted by ' . ($user->name ?? 'Unknown User');
            ProductLog::create([
                'note' => $note,
                'product_name' => $product->name,
                'product_id' => $product->id,
                'user_id' => Auth::id(),
            ]);

            // Delete old images if they exist
            if ($product->thumnail_img && Storage::disk('public')->exists($product->thumnail_img)) {
                Storage::disk('public')->delete($product->thumnail_img);
            }

            if ($product->gallary_img) {
                $galleryImages = json_decode($product->gallary_img, true) ?? [];
                foreach ($galleryImages as $image) {
                    if ($image && Storage::disk('public')->exists($image)) {
                        Storage::disk('public')->delete($image);
                    }
                }
            }

            $product->product_translations()->delete();
            $product->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Product deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product deletion failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong! Please try again.');
        }
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return redirect()->back()->with('error', 'Product not found.');
        }

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'name')->whereNull('deleted_at')->ignore($id),
            ],
            'description' => 'required|string',
            'brand_id' => 'required|exists:brands,id',
            'category_id' => 'required|exists:categories,id',
            'thumnail_img' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:1048',
            'gallary_img' => 'nullable',
            'gallary_img.*' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5048',
            'stock' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'discount' => 'nullable|integer|min:0|max:100',
            'final_price' => 'required|numeric|min:0',
            'feature' => 'nullable|boolean',
            'barcode' => 'nullable|string|max:255|unique:products,barcode,' . $id,
            'existing_gallary_img' => 'nullable|array',
        ]);

        DB::beginTransaction();

        try {
            $oldName = $product->name;

            // Store original values for logging
            $oldPurchasePrice = $product->purchase_price;
            $oldSalePrice = $product->sale_price;
            $oldDiscount = $product->discount;
            $oldFinalPrice = $product->final_price;

            // Handle thumbnail image
            $thumbnailPath = $product->thumnail_img;
            if ($request->hasFile('thumnail_img')) {
                if ($thumbnailPath && Storage::disk('public')->exists($thumbnailPath)) {
                    Storage::disk('public')->delete($thumbnailPath);
                }
                $thumbnailPath = $request->file('thumnail_img')->store('products/thumbnails', 'public');
            }

            // Gallery Image Handling
            $oldGallery = json_decode($product->gallary_img, true) ?? [];
            $newExistingGallery = $request->input('existing_gallary_img', []);

            // ✅ Remove deleted images from storage
            foreach ($oldGallery as $oldImage) {
                if (!in_array($oldImage, $newExistingGallery)) {
                    if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                        Storage::disk('public')->delete($oldImage);
                    }
                }
            }

            // ✅ Add new uploaded images
            $galleryPaths = $newExistingGallery;
            if ($request->hasFile('gallary_img')) {
                foreach ($request->file('gallary_img') as $image) {
                    $galleryPaths[] = $image->store('products/gallery', 'public');
                }
            }

            // Update product
            $product->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'brand_id' => $request->brand_id,
                'category_id' => $request->category_id,
                'thumnail_img' => $thumbnailPath,
                'gallary_img' => json_encode($galleryPaths),
                'stock' => $request->stock,
                'status' => $request->status,
                'purchase_price' => $request->purchase_price,
                'sale_price' => $request->sale_price,
                'discount' => $request->discount ?? 0,
                'final_price' => $request->final_price,
                'feature' => $request->feature ?? false,
                'barcode' => $request->barcode,
            ]);
            return redirect()->back()->with('success', 'Product updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong! Please try again.');
        }
    }


    public function product_log()
    {
        $locale = session('locale', App::getLocale());
        $productLogs = ProductLog::with('user')->where('user_id', Auth::id())->orderBy('created_at', 'desc')->paginate(10);
        return Inertia::render('admin/product/ProductLog', [
            'productLog' => $productLogs,
            'translations' => __('messages'),
            'locale' => $locale,
        ]);
    }

    // ProductController.php
public function productSearch(Request $request)
{
    try {
        $locale = session('locale', App::getLocale());
        $query = $request->input('query');

        $products = Product::where('status', 1)
            ->where(function ($q) use ($query, $locale) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhereHas('product_translations', function ($q) use ($query, $locale) {
                      $q->where('lang', $locale)->where('name', 'like', "%{$query}%");
                  });
            })
            ->with([
                'category' => fn($q) => $q->with(['category_translations' => fn($q) => $q->where('lang', $locale)]),
                'product_translations' => fn($q) => $q->where('lang', $locale),
            ])
            ->get()
            ->map(function ($product) use ($locale) {
                return [
                    'id' => $product->id,
                    'name' => $product->product_translations->first()?->name ?? $product->name,
                    'slug' => $product->slug,
                    'thumnail_img' => $product->thumnail_img,
                    'sale_price' => $product->sale_price,
                    'final_price' => $product->final_price,
                ];
            });

        return response()->json([
            'products' => $products,
        ]);
    } catch (\Throwable $e) {
        \Log::error('Failed to search products: ' . $e->getMessage());
        return response()->json(['error' => 'Something went wrong while searching products.'], 500);
    }
}


}
