<?php

namespace App\Imports;

use App\Models\ImportData;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DataImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $imagePath = null;

        // Handle image path if provided
        if (isset($row['image']) && !empty($row['image'])) {
            $imagePath = $this->processImagePath($row['image']);
        }

        return new ImportData([
            'name' => $row['name'] ?? '',
            'image' => $imagePath,
        ]);
    }

    /**
     * Process image path from local machine and copy to storage
     *
     * @param string $localPath
     * @return string|null
     */
    private function processImagePath($localPath)
    {
        try {
            // Clean the path and handle different path formats
            $localPath = trim($localPath);

            // Handle Windows paths (convert backslashes to forward slashes)
            $localPath = str_replace('\\', '/', $localPath);

                        // Check if file exists
            if (!file_exists($localPath)) {
                Log::warning("Image file not found: {$localPath}");
                return null;
            }

            // Get file extension
            $extension = pathinfo($localPath, PATHINFO_EXTENSION);
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array(strtolower($extension), $allowedExtensions)) {
                Log::warning("Invalid image extension: {$extension} for file: {$localPath}");
                return null;
            }

            // Generate unique filename
            $filename = 'import_' . time() . '_' . Str::random(10) . '.' . $extension;

            // Copy file to storage
            $storagePath = 'imports/' . $filename;

            if (Storage::disk('public')->put($storagePath, file_get_contents($localPath))) {
                return $storagePath;
            }

            Log::error("Failed to copy image to storage: {$localPath}");
            return null;

        } catch (\Exception $e) {
            Log::error("Error processing image path {$localPath}: " . $e->getMessage());
            return null;
        }
    }
}
