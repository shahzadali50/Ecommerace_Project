<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\CategoryLog;
use App\Jobs\TranslateCategory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Validation\Rule;

class CategoryImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, WithBatchInserts, WithChunkReading
{
    use SkipsErrors;

    private $importedCount = 0;
    private $errors = [];

    public function model(array $row)
    {
        try {
            // Skip empty rows
            if (empty($row['name']) || empty($row['description'])) {
                return null;
            }

            // Trim whitespace
            $name = trim($row['name']);
            $description = trim($row['description']);

            // Validate name length
            if (strlen($name) > 255) {
                $this->errors[] = "Category name '{$name}' is too long (max 255 characters). Skipped.";
                return null;
            }

            // Validate description length
            if (strlen($description) > 1000) {
                $this->errors[] = "Category description for '{$name}' is too long (max 1000 characters). Skipped.";
                return null;
            }

            // Check if category already exists
            $existingCategory = Category::where('user_id', Auth::id())
                ->where('name', $name)
                ->first();

            if ($existingCategory) {
                $this->errors[] = "Category '{$name}' already exists. Skipped.";
                return null;
            }

            // Create category
            $category = Category::create([
                'user_id' => Auth::id(),
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => $description,
                'image' => null, // Default image will be set later
            ]);

            // Log category creation
            CategoryLog::create([
                'note' => 'Category "' . $category->name . '" imported via Excel by ' . (Auth::user()->name ?? 'Unknown'),
                'category_id' => $category->id,
                'category_name' => $category->name,
                'user_id' => Auth::id(),
            ]);

            // Dispatch translation job
            TranslateCategory::dispatch($category);

            $this->importedCount++;

            return $category;

        } catch (\Exception $e) {
            Log::error('Category import error: ' . $e->getMessage(), [
                'row' => $row,
                'user_id' => Auth::id()
            ]);

            $this->errors[] = "Error importing category '{$row['name']}': " . $e->getMessage();
            return null;
        }
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->where('user_id', Auth::id())->whereNull('deleted_at'),
            ],
            'description' => 'required|string|max:1000',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.required' => 'Category name is required.',
            'name.unique' => 'Category name already exists.',
            'description.required' => 'Category description is required.',
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
