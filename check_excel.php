<?php
require 'vendor/autoload.php';

use Maatwebsite\Excel\Excel as BaseExcel;
use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    $spreadsheet = IOFactory::load('C:/Users/User/Downloads/employee_template.xlsx');
    $sheet = $spreadsheet->getActiveSheet();
    $data = $sheet->toArray();

    echo "Total rows in Excel: " . count($data) . PHP_EOL;

    if (count($data) > 0) {
        echo "Headers (row 0): " . json_encode($data[0]) . PHP_EOL;

        if (count($data) > 1) {
            echo "First data row (row 1): " . json_encode($data[1]) . PHP_EOL;
        }

        if (count($data) > 2) {
            echo "Second data row (row 2): " . json_encode($data[2]) . PHP_EOL;
        }
    } else {
        echo "Excel file appears to be empty!" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "Error reading Excel: " . $e->getMessage() . PHP_EOL;
}
