<?php

namespace App\Http\Controllers;

use Str;
use Exception;
use Inertia\Inertia;
use App\Models\Brand;
use App\Models\BrandLog;
use App\Models\Category;
use App\Jobs\TranslateBrand;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\BrandTranslation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class BrandController extends Controller
{

      public function index()
{
    try {
        $locale = session('locale', App::getLocale());

        // Load brand and category translations for current locale
        $brands = Brand::with([
            'brand_translations' => fn($q) => $q->where('lang', $locale),
            'category' => fn($q) => $q->with(['category_translations' => fn($q) => $q->where('lang', $locale)])
        ])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        // Transform the collection to include translated names
        $brands = $brands->map(function ($brand) use ($locale) {
            return [
                'id' => $brand->id,
                'slug' => $brand->slug,
                'image' => $brand->image,
                'created_at' => $brand->created_at->format('Y-m-d H:i'),
                'name' => $brand->brand_translations->first()?->name ?? $brand->name,
                'description' => $brand->brand_translations->first()?->description ?? $brand->description,
                'category_id' => $brand->category_id, // For edit modal
                'category_name' => $brand->category?->category_translations->first()?->name ?? $brand->category?->name ?? 'N/A',
            ];
        });

        // Load categories with translations
        $categories = Category::with(['category_translations' => fn($q) => $q->where('lang', $locale)])
            ->get()
            ->map(function ($category) use ($locale) {
                return [
                    'id' => $category->id,
                    'name' => $category->category_translations->first()?->name ?? $category->name,
                ];
            });

        return Inertia::render('admin/brand/Index', [
            'brands' => ['data' => $brands], // Wrap in data key
            'categories' => ['data' => $categories], // Wrap in data key
            'translations' => __('messages'),
            'locale' => $locale,
        ]);
    } catch (\Throwable $e) {
        \Log::error('Failed to load brands in index(): ' . $e->getMessage());
        return redirect()->back()->with('error', 'Something went wrong while loading brands.');
    }
}

public function related_brand_list($slug)
{
    try {
        $locale = session('locale', App::getLocale());

        // Get the category by slug
        $category = Category::where('slug', $slug)->first();

        if (!$category) {
            return redirect()->back()->with('error', 'Record Not Found');
        }

        // Load related brands with translations, user, category
        $brands = $category->brands()
            ->with([
                'brand_translations' => fn($query) => $query->where('lang', $locale),
                'user',
                'category'
            ])
            ->orderByDesc('created_at')
            ->paginate(10);

        // Apply fallback logic for translated name/description
        $brands->getCollection()->transform(fn($brand) => [
            'id' => $brand->id,
            'slug' => $brand->slug,
            'image' => $brand->image,
            'created_at' => $brand->created_at->format('Y-m-d H:i'),
            'name' => $brand->brand_translations->first()?->name ?? $brand->name,
            'description' => $brand->brand_translations->first()?->description ?? $brand->description,
            'category_name' => $brand->category?->name ?? 'N/A',
            'user_name' => $brand->user?->name ?? 'N/A',
        ]);

        return Inertia::render('admin/brand/CategoryBrandList', [
            'brands' => $brands,
            'category' => $category,
            'translations' => __('messages'),
            'locale' => $locale,
        ]);

    } catch (\Throwable $e) {
        \Log::error("Failed to load brands for category slug [$slug]: " . $e->getMessage());

        return redirect()->back()->with('error', 'Something went wrong while loading related brands.');
    }
}


    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('brand_translations', 'name')->where('user_id', Auth::id())->whereNull('deleted_at'),
            ],
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        DB::beginTransaction();

        try {
            // Upload image
            $originalName = $request->file('image')->getClientOriginalName();
            $filename = pathinfo($originalName, PATHINFO_FILENAME) . '_' . substr(md5(uniqid()), 0, 6) . '.' . pathinfo($originalName, PATHINFO_EXTENSION);
            $imagePath = $request->file('image')->storeAs('brands', $filename, 'public');

            // Create brand
            $brand = Brand::create([
                'user_id' => Auth::id(),
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'category_id' => $request->category_id,
                'image' => $imagePath,
            ]);

            // Log brand creation
            $user = Auth::user();
            BrandLog::create([
                'note' => 'Brand "' . $brand->name . '" created by ' . ($user->name ?? 'Unknown'),
                'brand_id' => $brand->id,
                'brand_name' => $brand->name,
                'user_id' => $user->id,
            ]);

            // Dispatch translation job for the brand
            TranslateBrand::dispatch($brand);

            DB::commit();
            return redirect()->back()->with('success', 'Brand created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Brand creation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong! Please try again.');
        }
    }


    public function update(Request $request, $id)
{
    $locale = session('locale', App::getLocale());

    $brand = Brand::find($id);
    if (!$brand) {
        return redirect()->back()->with('error', 'Brand not found.');
    }

    // 🔍 Get current brand translation to ignore in unique validation
    $currentTranslation = BrandTranslation::where('brand_id', $id)
        ->where('lang', $locale)
        ->where('user_id', Auth::id())
        ->first();

    // ✅ Validate with correct unique rule on translation table
    $request->validate([
        'name' => [
            'required', 'string', 'max:255',
            Rule::unique('brand_translations', 'name')
                ->ignore($currentTranslation?->id)
                ->where(function ($query) use ($locale) {
                    $query->where('lang', $locale)
                        ->where('user_id', Auth::id())
                        ->whereNull('deleted_at');
                }),
        ],
        'description' => 'required|string',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
    ]);

    DB::beginTransaction();
    try {
        $oldName = $brand->name;

        $updateData = [
            'name' => $request->name,
            'description' => $request->description,
            'slug' => \Str::slug($request->name),
        ];

        // 🖼️ Handle image replacement
        if ($request->hasFile('image')) {
            if ($brand->image && Storage::disk('public')->exists($brand->image)) {
                Storage::disk('public')->delete($brand->image);
            }

            $originalName = $request->file('image')->getClientOriginalName();
            $filename = pathinfo($originalName, PATHINFO_FILENAME) . '_' . substr(md5(uniqid()), 0, 6) . '.' . pathinfo($originalName, PATHINFO_EXTENSION);
            $imagePath = $request->file('image')->storeAs('brands', $filename, 'public');
            $updateData['image'] = $imagePath;
        }

        $brand->update($updateData);

        // 📝 Log the update
        $user = Auth::user();
        $note = 'Brand "' . $oldName . '" updated to "' . $brand->name . '" by ' . ($user->name ?? 'Unknown User');
        BrandLog::create([
            'note' => $note,
            'brand_id' => $brand->id,
            'brand_name' => $brand->name,
            'user_id' => $user->id,
        ]);

        // 🔁 Dispatch translation job
        TranslateBrand::dispatch($brand);

        DB::commit();
        return redirect()->back()->with('success', 'Brand updated successfully.');
    } catch (\Throwable $e) {
        DB::rollBack();
        \Log::error('Brand update failed: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Something went wrong! Please try again.');
    }
}


    public function destroy($id)
    {
        try {
            $brand = Brand::find($id);

            if (!$brand) {
                return redirect()->back()->with('error', 'Brand not found.');
            }

            DB::beginTransaction();

            $user = Auth::user();

            // 📝 Log the deletion
            $note = 'Brand "' . $brand->name . '" Deleted by ' . ($user->name ?? 'Unknown User');
            BrandLog::create([
                'note' => $note,
                'brand_name' => $brand->name,
                'brand_id' => $brand->id,
                'user_id' => Auth::id(),
            ]);

            // 🗑️ Delete image from storage
            if ($brand->image && Storage::disk('public')->exists($brand->image)) {
                Storage::disk('public')->delete($brand->image);
            }

            // 🧹 Delete related products
            $brand->products()->delete();

            // 🧹 Delete brand translations
            $brand->brand_translations()->delete();

            // 💥 Delete the brand itself
            $brand->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Brand deleted successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Brand deletion failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong! Please try again.');
        }
    }
    public function brand_log(){

        $brandLog = BrandLog::with('user') // Eager load user relation first
        ->where('user_id', Auth::id()) // Filter for logged-in user
        ->latest() // Same as orderBy('created_at', 'desc')
        ->paginate(10);

        return Inertia::render('admin/brand/BrandLog', [
            'BrandLog' => $brandLog,
        ]);
    }
    
}
