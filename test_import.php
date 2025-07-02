<?php

// Simple test script to verify import functionality
require_once __DIR__ . '/vendor/autoload.php';

use App\Imports\DataImport;
use Maatwebsite\Excel\Facades\Excel;

// Test data
$testData = [
    ['name' => 'Test Category 1', 'image' => 'C:\Users\TestUser\Pictures\test1.jpg'],
    ['name' => 'Test Category 2', 'image' => 'C:\Users\TestUser\Pictures\test2.png'],
    ['name' => 'Test Category 3', 'image' => ''], // No image
];

echo "Testing DataImport class...\n";

try {
    $import = new DataImport();

    foreach ($testData as $row) {
        $result = $import->model($row);
        echo "Processed: " . $row['name'] . " - Image: " . ($result->image ?? 'None') . "\n";
    }

    echo "Test completed successfully!\n";
} catch (Exception $e) {
    echo "Test failed: " . $e->getMessage() . "\n";
}
