<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception as ReaderException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExcelProcessingService
{
    public function parseProductUpdateFile($filePath)
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

    private function parseCsvFile($fullPath)
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
                        throw new \Exception('CSV file must contain SKU and product title columns. Current columns: ' . implode(', ', $headers) . '. Found SKU column: ' . ($skuIndex >= 0 ? 'Yes' : 'No') . ', Found title column: ' . ($titleIndex >= 0 ? 'Yes' : 'No')