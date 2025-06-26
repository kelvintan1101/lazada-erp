<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception as ReaderException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExcelProcessingService
{
    /**
     * 解析Excel文件并提取产品数据
     * 
     * @param string $filePath 文件路径
     * @return array 解析结果
     */
    public function parseProductUpdateFile($filePath)
    {
        try {
            // 检查文件是否存在
            if (!Storage::exists($filePath)) {
                throw new \Exception('文件不存在');
            }

            $fullPath = Storage::path($filePath);
            
            // 加载Excel文件
            $spreadsheet = IOFactory::load($fullPath);
            $worksheet = $spreadsheet->getActiveSheet();
            
            // 获取最高行数
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();
            
            Log::info('Excel文件解析开始', [
                'file_path' => $filePath,
                'highest_row' => $highestRow,
                'highest_column' => $highestColumn
            ]);

            // 读取表头（第一行）
            $headers = [];
            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $headers[$col] = $worksheet->getCell($col . '1')->getValue();
            }

            // 验证必需的列是否存在
            $requiredColumns = $this->findRequiredColumns($headers);
            if (!$requiredColumns['sku'] || !$requiredColumns['title']) {
                throw new \Exception('Excel文件必须包含SKU和产品标题列。请确保文件包含"SKU"和"产品标题"或"Product Title"列。');
            }

            // 解析数据行
            $products = [];
            $errors = [];
            
            for ($row = 2; $row <= $highestRow; $row++) {
                try {
                    $sku = trim($worksheet->getCell($requiredColumns['sku'] . $row)->getValue());
                    $title = trim($worksheet->getCell($requiredColumns['title'] . $row)->getValue());

                    // 验证数据
                    if (empty($sku)) {
                        $errors[] = "第{$row}行：SKU不能为空";
                        continue;
                    }

                    if (empty($title)) {
                        $errors[] = "第{$row}行：产品标题不能为空";
                        continue;
                    }

                    // 验证标题长度（Lazada通常限制标题长度）
                    if (strlen($title) > 255) {
                        $errors[] = "第{$row}行：产品标题过长（超过255字符）";
                        continue;
                    }

                    $products[] = [
                        'sku' => $sku,
                        'title' => $title,
                        'row' => $row
                    ];

                } catch (\Exception $e) {
                    $errors[] = "第{$row}行：数据解析错误 - " . $e->getMessage();
                }
            }

            Log::info('Excel文件解析完成', [
                'total_products' => count($products),
                'total_errors' => count($errors)
            ]);

            return [
                'success' => true,
                'products' => $products,
                'errors' => $errors,
                'total_rows' => $highestRow - 1, // 减去表头行
                'valid_products' => count($products)
            ];

        } catch (ReaderException $e) {
            Log::error('Excel文件读取失败', [
                'file_path' => $filePath,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => '无法读取Excel文件：' . $e->getMessage(),
                'products' => [],
                'errors' => []
            ];

        } catch (\Exception $e) {
            Log::error('Excel文件处理失败', [
                'file_path' => $filePath,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'products' => [],
                'errors' => []
            ];
        }
    }

    private function findRequiredColumns($headers)
{
    $columns = [
        'sku' => null,
        'title' => null
    ];

    foreach ($headers as $col => $header) {
        $header = strtolower(trim($header));
        
        // 查找SKU列 - 支持更多格式
        if (in_array($header, [
            'sku', 'skuid', 'sku id', 'seller sku', 'sellersku',
            '卖家sku', '商品sku', 'sku编号', 'sku号'
        ])) {
            $columns['sku'] = $col;
        }
        
        // 查找标题列 - 支持更多格式  
        if (in_array($header, [
            'title', 'product title', 'name', 'product name', 'productname',
            '产品标题', '商品标题', '产品名称', '商品名称', 'product_name', 'product_title'
        ])) {
            $columns['title'] = $col;
        }
    }

    return $columns;
}

    /**
     * 验证Excel文件格式
     * 
     * @param string $filePath 文件路径
     * @return array 验证结果
     */
    public function validateExcelFile($filePath)
    {
        try {
            if (!Storage::exists($filePath)) {
                return [
                    'valid' => false,
                    'message' => '文件不存在'
                ];
            }

            $fullPath = Storage::path($filePath);
            $fileExtension = pathinfo($fullPath, PATHINFO_EXTENSION);
            
            if (!in_array(strtolower($fileExtension), ['xlsx', 'xls', 'csv'])) {
                return [
                    'valid' => false,
                    'message' => '不支持的文件格式。请上传Excel文件（.xlsx, .xls）或CSV文件。'
                ];
            }

            // 尝试读取文件
            $spreadsheet = IOFactory::load($fullPath);
            $worksheet = $spreadsheet->getActiveSheet();
            
            // 检查是否有数据
            if ($worksheet->getHighestRow() < 2) {
                return [
                    'valid' => false,
                    'message' => '文件中没有数据行'
                ];
            }

            return [
                'valid' => true,
                'message' => '文件格式正确'
            ];

        } catch (\Exception $e) {
            return [
                'valid' => false,
                'message' => '文件验证失败：' . $e->getMessage()
            ];
        }
    }
}
