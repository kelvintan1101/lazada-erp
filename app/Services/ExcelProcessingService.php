<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception as ReaderException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExcelProcessingService
{
    public function parseProductUpdateFile($filePath): array
    {
        try {
            \Log::info('Starting to parse file', ['file_path' => $filePath]);

            // Check if file exists
            if (!Storage::exists($filePath)) {
                throw new \Exception('File does not exist: ' . $filePath);
            }

            $fullPath = Storage::path($filePath);
            \Log::info('File complete path', [
                'full_path' => $fullPath,
                'file_exists' => file_exists($fullPath),
                'is_readable' => is_readable($fullPath),
                'file_size' => file_exists($fullPath) ? filesize($fullPath) : 0
            ]);

            if (!file_exists($fullPath)) {
                throw new \Exception('File physical path does not exist: ' . $fullPath);
            }

            if (!is_readable($fullPath)) {
                throw new \Exception('File is not readable: ' . $fullPath);
            }

            // Detect file type
            $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
            \Log::info('File extension detection', ['extension' => $extension]);

            if ($extension === 'csv') {
                return $this->parseCsvFile($fullPath);
            } else {
                return $this->parseExcelFile($fullPath);
            }

        } catch (ReaderException $e) {
            Log::error('Excel file reading failed', [
                'file_path' => $filePath,
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);

            return [
                'success' => false,
                'message' => 'Unable to read Excel file: ' . $e->getMessage(),
                'products' => [],
                'errors' => []
            ];

        } catch (\Exception $e) {
            Log::error('Excel file processing failed', [
                'file_path' => $filePath,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file_location' => $e->getFile()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'products' => [],
                'errors' => []
            ];
        }
    }

    private function parseCsvFile($fullPath): array
    {
        \Log::info('Starting to parse CSV file', ['path' => $fullPath]);

        $products = [];
        $errors = [];
        $rowNumber = 0;
        $headers = [];

        if (($handle = fopen($fullPath, 'r')) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                $rowNumber++;

                if ($rowNumber === 1) {
                    // Process header
                    $headers = array_map('trim', $data);
                    \Log::info('CSV headers', ['headers' => $headers]);

                    // Validate if required columns exist
                    $skuIndex = -1;
                    $titleIndex = -1;

                    foreach ($headers as $index => $header) {
                        $header = strtolower(trim($header));
                        if (in_array($header, ['sku', 'skuid', 'sku id', 'seller sku', 'sellersku', 'seller_sku', 'product_sku'])) {
                            $skuIndex = $index;
                        }
                        if (in_array($header, ['title', 'product title', 'name', 'product name', 'product_title', 'product_name'])) {
                            $titleIndex = $index;
                        }
                    }

                    if ($skuIndex === -1 || $titleIndex === -1) {
                        throw new \Exception('CSV file must contain SKU and product title columns. Current columns: ' . implode(', ', $headers) . '. Found SKU column: ' . ($skuIndex >= 0 ? 'Yes' : 'No') . ', Found title column: ' . ($titleIndex >= 0 ? 'Yes' : 'No'));
                    }
                    continue;
                }

                // Process data rows
                if (count($data) < count($headers)) {
                    $errors[] = "Row {$rowNumber}: Data column count mismatch";
                    continue;
                }

                // Get SKU and title using index directly
                $sku = isset($data[$skuIndex]) ? trim($data[$skuIndex]) : '';
                $title = isset($data[$titleIndex]) ? trim($data[$titleIndex]) : '';

                if (empty($sku)) {
                    $errors[] = "Row {$rowNumber}: SKU cannot be empty";
                    continue;
                }

                if (empty($title)) {
                    $errors[] = "Row {$rowNumber}: Product title cannot be empty";
                    continue;
                }

                $products[] = [
                    'sku' => $sku,
                    'title' => $title,
                    'row' => $rowNumber
                ];
            }
            fclose($handle);
        } else {
            throw new \Exception('Unable to open CSV file');
        }

        \Log::info('CSV parsing completed', [
            'total_products' => count($products),
            'total_errors' => count($errors)
        ]);
        
        return [
            'success' => true,
            'products' => $products,
            'errors' => $errors,
            'total_rows' => $rowNumber - 1,
            'valid_products' => count($products)
        ];
    }

    private function parseExcelFile($fullPath): array
    {
        \Log::info('Starting to parse Excel file', ['path' => $fullPath]);

        // Load Excel file
        $spreadsheet = IOFactory::load($fullPath);
        $worksheet = $spreadsheet->getActiveSheet();

        // Get highest row number
        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();

        Log::info('Excel file parsing started', [
            'highest_row' => $highestRow,
            'highest_column' => $highestColumn
        ]);

        // Read header (first row)
        $headers = [];
        for ($col = 'A'; $col <= $highestColumn; $col++) {
            $headers[$col] = $worksheet->getCell($col . '1')->getValue();
        }

        // Validate if required columns exist
        $requiredColumns = $this->findRequiredColumns($headers);
        if (!$requiredColumns['sku'] || !$requiredColumns['title']) {
            throw new \Exception('Excel file must contain SKU and product title columns. Please ensure the file contains "SKU" and "Product Title" columns.');
        }

        // Parse data rows
        $products = [];
        $errors = [];

        for ($row = 2; $row <= $highestRow; $row++) {
            try {
                $sku = trim($worksheet->getCell($requiredColumns['sku'] . $row)->getValue());
                $title = trim($worksheet->getCell($requiredColumns['title'] . $row)->getValue());

                // Validate data
                if (empty($sku)) {
                    $errors[] = "Row {$row}: SKU cannot be empty";
                    continue;
                }

                if (empty($title)) {
                    $errors[] = "Row {$row}: Product title cannot be empty";
                    continue;
                }

                // Validate title length (Lazada usually limits title length)
                if (strlen($title) > 255) {
                    $errors[] = "Row {$row}: Product title too long (exceeds 255 characters)";
                    continue;
                }

                $products[] = [
                    'sku' => $sku,
                    'title' => $title,
                    'row' => $row
                ];

            } catch (\Exception $e) {
                $errors[] = "Row {$row}: Data parsing error - " . $e->getMessage();
            }
        }

        Log::info('Excel file parsing completed', [
            'total_products' => count($products),
            'total_errors' => count($errors)
        ]);

        return [
            'success' => true,
            'products' => $products,
            'errors' => $errors,
            'total_rows' => $highestRow - 1, // Subtract header row
            'valid_products' => count($products)
        ];
    }

    private function findRequiredColumns($headers): array
{
    $columns = [
        'sku' => null,
        'title' => null
    ];

    foreach ($headers as $col => $header) {
        $header = strtolower(trim($header));
        
        // Find SKU column - support more formats
        if (in_array($header, [
            'sku', 'skuid', 'sku id', 'seller sku', 'sellersku',
            'seller_sku', 'product_sku', 'sku_id', 'sku_number'
        ])) {
            $columns['sku'] = $col;
        }

        // Find title column - support more formats
        if (in_array($header, [
            'title', 'product title', 'name', 'product name', 'productname',
            'product_title', 'product_name', 'item_title', 'item_name'
        ])) {
            $columns['title'] = $col;
        }
    }

    return $columns;
}

    /**
     * Validate Excel file format
     *
     * @param string $filePath File path
     * @return array Validation result
     */
    public function validateExcelFile($filePath): array
    {
        try {
            if (!Storage::exists($filePath)) {
                return [
                    'valid' => false,
                    'message' => 'File does not exist'
                ];
            }

            $fullPath = Storage::path($filePath);
            $fileExtension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

            if (!in_array($fileExtension, ['xlsx', 'xls', 'csv'])) {
                return [
                    'valid' => false,
                    'message' => 'Unsupported file format. Please upload Excel files (.xlsx, .xls) or CSV files.'
                ];
            }

            // For CSV files, use simple validation
            if ($fileExtension === 'csv') {
                return $this->validateCsvFile($fullPath);
            }

            // For Excel files, try using PhpSpreadsheet
            try {
                $spreadsheet = IOFactory::load($fullPath);
                $worksheet = $spreadsheet->getActiveSheet();

                // Check if there is data
                if ($worksheet->getHighestRow() < 2) {
                    return [
                        'valid' => false,
                        'message' => 'No data rows in file'
                    ];
                }

                return [
                    'valid' => true,
                    'message' => 'File format is correct'
                ];
            } catch (\Exception $e) {
                return [
                    'valid' => false,
                    'message' => 'Excel file validation failed: ' . $e->getMessage() . '. Please ensure PhpSpreadsheet package is properly installed.'
                ];
            }

        } catch (\Exception $e) {
            return [
                'valid' => false,
                'message' => 'File validation failed: ' . $e->getMessage()
            ];
        }
    }

    private function validateCsvFile($fullPath): array
    {
        try {
            if (!is_readable($fullPath)) {
                return [
                    'valid' => false,
                    'message' => 'CSV file is not readable'
                ];
            }

            $rowCount = 0;
            if (($handle = fopen($fullPath, 'r')) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                    $rowCount++;
                    if ($rowCount >= 2) break; // Only need to check if there is header and at least one data row
                }
                fclose($handle);
            }

            if ($rowCount < 2) {
                return [
                    'valid' => false,
                    'message' => 'No data rows in CSV file'
                ];
            }

            return [
                'valid' => true,
                'message' => 'CSV file format is correct'
            ];

        } catch (\Exception $e) {
            return [
                'valid' => false,
                'message' => 'CSV file validation failed: ' . $e->getMessage()
            ];
        }
    }
}