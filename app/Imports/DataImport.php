<?php

namespace App\Imports;

use App\Models\ImportData;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;



class DataImport implements ToModel,WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $localPath = $row['image']; // e.g., C:\Users\you\Pictures\img.jpg
        $newName = basename($localPath); // shahzad.jpeg

        // Target path inside 'storage/app/public/products/thumbnails'
        $storagePath = 'imports';

        if (File::exists($localPath)) {
            // Ensure the directory exists
            Storage::disk('public')->makeDirectory($storagePath);

            // Put file into storage/app/public/products/thumbnails
            Storage::disk('public')->putFileAs(
                $storagePath,
                new \Illuminate\Http\File($localPath),
                $newName
            );

            $finalPath = $storagePath . '/' . $newName; // For DB storage
        } else {
            $finalPath = null;
        }

        return new ImportData([
            'name'  => $row['name'],
            'image' => $finalPath,
        ]);
    }

}
