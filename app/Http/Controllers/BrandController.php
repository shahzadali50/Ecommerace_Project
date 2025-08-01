<?php

namespace App\Http\Controllers;

use Str;
use Exception;
use Inertia\Inertia;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
        $brands = Brand::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        // Transform the collection with basic data
        $brands = $brands->map(function ($brand) {
            return [
                'id' => $brand->id,
                'slug' => $brand->slug,
                'image' => $brand->image,
                'created_at' => $brand->created_at->format('Y-m-d H:i'),
                'name' => $brand->name,
                'description' => $brand->description,
            ];
        });
        return Inertia::render('admin/brand/Index', [
            'brands' => ['data' => $brands],
            'locale' => App::getLocale(),
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

        // Load related brands with user, category
        $brands = $category->brands()
            ->with(['user', 'category'])
            ->orderByDesc('created_at')
            ->paginate(10);

        // Transform with basic data
        $brands->getCollection()->transform(fn($brand) => [
            'id' => $brand->id,
            'slug' => $brand->slug,
            'image' => $brand->image,
            'created_at' => $brand->created_at->format('Y-m-d H:i'),
            'name' => $brand->name,
            'description' => $brand->description,
            'category_name' => $brand->category?->name ?? 'N/A',
            'user_name' => $brand->user?->name ?? 'N/A',
        ]);

        return Inertia::render('admin/brand/CategoryBrandList', [
            'brands' => $brands,
            'category' => $category,
            'locale' => App::getLocale(),
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
                Rule::unique('brands', 'name')->where('user_id', Auth::id())->whereNull('deleted_at'),
            ],
            'description' => 'required|string',
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
                'image' => $imagePath,
            ]);

            // Brand created successfully

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

    $brand = Brand::find($id);
    if (!$brand) {
        return redirect()->back()->with('error', 'Brand not found.');
    }

    // ✅ Validate with correct unique rule on brands table
    $request->validate([
        'name' => [
            'required', 'string', 'max:255',
            Rule::unique('brands', 'name')
                ->ignore($id)
                ->where('user_id', Auth::id())
                ->whereNull('deleted_at'),
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

        // Brand updated successfully

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

            // 🗑️ Delete image from storage
            if ($brand->image && Storage::disk('public')->exists($brand->image)) {
                Storage::disk('public')->delete($brand->image);
            }

            // 🧹 Delete related products
            $brand->products()->delete();

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


}
