<?php

namespace App\Imports;

use App\Models\ImportData;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\File;



class DataImport implements ToModel,WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $localPath = $row['image']; // Local absolute path from Excel
        $newName = basename($localPath); // Get filename only (e.g., shahzad.jpeg)
        $uploadDir = public_path('uploads');
        $publicPath = $uploadDir . '/' . $newName;

        // Ensure the uploads directory exists
        if (!File::exists($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true);
        }

        // Copy the file if it exists at the source
        if (File::exists($localPath)) {
            File::copy($localPath, $publicPath);
        }

        return new ImportData([
            'name'  => $row['name'],
            'image' => 'uploads/' . $newName,
        ]);
    }

}
