<?php

namespace App\Http\Controllers;

use Log;
use Exception;
use Inertia\Inertia;
use App\Models\Category;
use App\Models\CategoryLog;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Jobs\TranslateCategory;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\CategoryTranslation;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index()
{
    try {
        $locale = session('locale', App::getLocale());

        // Eager load translations for the current session language
        $categories = Category::with([
                'category_translations' => fn($q) => $q->where('lang', $locale)
            ])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        // Transform each category with translation fallback
        $categories = $categories->map(fn($category) => [
            'id' => $category->id,
            'slug' => $category->slug,
            'image' => $category->image,
            'created_at' => $category->created_at->format('Y-m-d H:i'),
            'name' => $category->category_translations->first()?->name ?? $category->name,
            'description' => $category->category_translations->first()?->description ?? $category->description,
        ]);

        return Inertia::render('admin/category/Index', [
            'categories' => [
                'data' => $categories // Wrap the collection in a data key
            ],
            'translations' => __('messages'),
            'locale' => App::getLocale(),
        ]);

    } catch (\Throwable $e) {
        \Log::error('Failed to load categories in index(): ' . $e->getMessage());
        return redirect()->back()->with('error', 'Something went wrong while loading categories.');
    }
}

    public function store(Request $request)
{
    $request->validate([
        'name' => [
            'required', 'string', 'max:255',
            Rule::unique('category_translations', 'name')->where('user_id', Auth::id())->whereNull('deleted_at'),
        ],
        'description' => 'required|string',
        'image' => [
            'required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048',
        ],
    ]);

    DB::beginTransaction();
    try {
        // Upload image
        $originalName = $request->file('image')->getClientOriginalName();
        $filename = pathinfo($originalName, PATHINFO_FILENAME) . '_' . substr(md5(uniqid()), 0, 6) . '.' . pathinfo($originalName, PATHINFO_EXTENSION);
        $imagePath = $request->file('image')->storeAs('categories', $filename, 'public');

        // Create category
        $category = Category::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'image' => $imagePath,
        ]);

        // Log category
        $user = Auth::user();
        CategoryLog::create([
            'note' => 'Category "' . $category->name . '" created by ' . ($user->name ?? 'Unknown'),
            'category_id' => $category->id,
            'category_name' => $category->name,
            'user_id' => $user->id,
        ]);


        // ✅ Dispatch translation job
        TranslateCategory::dispatch($category);


        DB::commit();
        return redirect()->back()->with('success', 'Category created successfully.');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Category creation failed: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Something went wrong! Please try again.');
    }
}




public function destroy($id)
{
    DB::beginTransaction();

    try {
        $category = Category::with([
            'brands.products.purchaseProducts',
            'brands.brand_translations',
            'category_translations'
        ])->find($id);

        if (!$category) {
            return redirect()->back()->with('error', 'Category not found.');
        }

        $user = Auth::user();

        // 🧼 Log deletion
        $note = 'Category "' . $category->name . '" Deleted by ' . ($user->name ?? 'Unknown User');
        CategoryLog::create([
            'note' => $note,
            'category_name' => $category->name,
            'category_id' => $category->id,
            'user_id' => $user->id,
        ]);

        // 🗑️ Delete category image
        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }

        // 💥 Delete the category (boot() method handles full cascade)
        $category->delete();

        DB::commit();

        return redirect()->back()->with('success', 'Category deleted successfully.');
    } catch (\Throwable $e) {
        DB::rollBack();

        \Log::error('Failed to delete category ID ' . $id . ': ' . $e->getMessage());

        return redirect()->back()->with('error', 'An error occurred while deleting the category.');
    }
}



    public function update(Request $request, $id)
    {
        $locale = session('locale', App::getLocale());

        // 🔍 Find category
        $category = Category::find($id);

        if (!$category) {
            return redirect()->back()->with('error', 'Category not found.');
        }

        // 🔍 Get the current translation row (needed for unique ignore)
        $currentTranslation = CategoryTranslation::where('category_id', $id)
            ->where('lang', $locale)
            ->where('user_id', Auth::id())
            ->first();

        // ✅ Validate input
        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('category_translations', 'name')
                    ->ignore($currentTranslation?->id) // ✅ this must be a translation ID
                    ->where(function ($query) use ($locale) {
                        $query->where('lang', $locale)
                              ->where('user_id', Auth::id())
                              ->whereNull('deleted_at');
                    }),
            ],
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $oldName = $category->name;

            $updateData = [
                'name' => $request->name,
                'description' => $request->description,
                'slug' => Str::slug($request->name),
            ];

            // Handle image update if provided
            if ($request->hasFile('image')) {
                if ($category->image && Storage::disk('public')->exists($category->image)) {
                    Storage::disk('public')->delete($category->image);
                }

                $originalName = $request->file('image')->getClientOriginalName();
                $filename = pathinfo($originalName, PATHINFO_FILENAME) . '_' . substr(md5(uniqid()), 0, 6) . '.' . pathinfo($originalName, PATHINFO_EXTENSION);
                $imagePath = $request->file('image')->storeAs('categories', $filename, 'public');
                $updateData['image'] = $imagePath;
            }

            $category->update($updateData);

            // ✅ Log the update
            $user = Auth::user();
            $note = 'Category "' . $oldName . '" updated to "' . $category->name . '" by ' . ($user->name ?? 'Unknown User');
            CategoryLog::create([
                'note' => $note,
                'category_id' => $category->id,
                'category_name' => $category->name,
                'user_id' => Auth::id(),
            ]);

            // ✅ Dispatch translation job (for updated content)
            TranslateCategory::dispatch($category);

            DB::commit();
            return redirect()->back()->with('success', 'Category updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Category update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong! Please try again.');
        }
    }

    public function category_log(){

        $CategoryLog = CategoryLog::with('user')
        ->where('user_id', Auth::id())
        ->latest()
          ->get();

        return Inertia::render('admin/category/CategoryLog', [
         'CategoryLog' => [
            'data' => $CategoryLog
        ],
        ]);
    }
}
