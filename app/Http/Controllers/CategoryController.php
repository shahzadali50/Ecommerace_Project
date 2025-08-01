<?php

namespace App\Http\Controllers;

use Log;
use Exception;
use Inertia\Inertia;
use App\Models\Category;
use App\Imports\DataImport;
use App\Models\CategoryLog;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Imports\CategoryImport;
use App\Jobs\TranslateCategory;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\CategoryTranslation;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;



class CategoryController extends Controller
{
    public function index()
    {
        try {
            // Eager load parent relationship
            $categories = Category::with(['parent'])
                ->where('user_id', Auth::id())
                ->latest()
                ->get();

            // Transform each category with basic data
            $categories = $categories->map(fn($category) => [
                'id' => $category->id,
                'image' => $category->image,
                'created_at' => $category->created_at->format('Y-m-d H:i'),
                'name' => $category->name,
                'description' => $category->description,
                'parent' => $category->parent ? [
                    'id' => $category->parent->id,
                    'name' => $category->parent->name,
                ] : null,
            ]);

            return Inertia::render('admin/category/Index', [
                'categories' => [
                    'data' => $categories // Wrap the collection in a data key
                ],
                'allCategories' => Category::where('user_id', Auth::id())
                    ->select('id', 'name')
                    ->get(),
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
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->where('user_id', Auth::id())->whereNull('deleted_at'),
            ],
            'description' => 'required|string',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:2048',
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
                'parent_id' => $request->parent_id,
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


            // Category created successfully


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
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')
                    ->ignore($id)
                    ->where('user_id', Auth::id())
                    ->whereNull('deleted_at'),
            ],
            'description' => 'required|string',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $oldName = $category->name;

            $updateData = [
                'name' => $request->name,
                'description' => $request->description,
                'parent_id' => $request->parent_id,
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

            // Category updated successfully

            DB::commit();
            return redirect()->back()->with('success', 'Category updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Category update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong! Please try again.');
        }
    }

    public function category_log()
    {

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
    public function import(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'file' => 'required|max:2048',
        ]);
        Excel::import(new DataImport, $request->file('file'));
        // dd('done');
        return redirect()->back()->with('success', 'Data imported successfully.');



    }
    // public function import(Request $request)
    // {
    //     dd($request->all());
    //     // Debug: Log the request data
    //     Log::info('Import request received', [
    //         'has_file' => $request->hasFile('excel_file'),
    //         'file_name' => $request->file('excel_file')?->getClientOriginalName(),
    //         'file_size' => $request->file('excel_file')?->getSize(),
    //         'file_mime' => $request->file('excel_file')?->getMimeType(),
    //         'all_files' => $request->allFiles(),
    //         'content_type' => $request->header('Content-Type'),
    //     ]);

    //     // Check if file exists before validation
    //     if (!$request->hasFile('excel_file')) {
    //         Log::error('No file uploaded');
    //         return redirect()->back()->with('error', 'No file uploaded');
    //     }

    //     $file = $request->file('excel_file');
    //     Log::info('File details', [
    //         'original_name' => $file->getClientOriginalName(),
    //         'mime_type' => $file->getMimeType(),
    //         'size' => $file->getSize(),
    //         'extension' => $file->getClientOriginalExtension(),
    //     ]);

    //     $request->validate([
    //         'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
    //     ]);

    //     try {
    //         $import = new CategoryImport();

    //         Excel::import($import, $request->file('excel_file'));

    //         $importedCount = $import->getImportedCount();
    //         $errors = $import->getErrors();

    //         $message = "Successfully imported {$importedCount} categories.";

    //         if (!empty($errors)) {
    //             $message .= " Errors: " . implode(', ', $errors);
    //         }

    //         return redirect()->back()->with('success', $message);

    //     } catch (\Exception $e) {
    //         Log::error('Category import failed: ' . $e->getMessage());
    //         return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
    //     }
    // }

}


