<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkUpdateTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'status',
        'file_path',
        'file_data',
        'total_items',
        'processed_items',
        'successful_items',
        'failed_items',
        'results',
        'errors',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'file_data' => 'array',
        'results' => 'array',
        'errors' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * 获取进度百分比
     */
    public function getProgressPercentage()
    {
        if ($this->total_items == 0) {
            return 0;
        }
        
        return round(($this->processed_items / $this->total_items) * 100, 2);
    }

    /**
     * 检查任务是否完成
     */
    public function isCompleted()
    {
        return in_array($this->status, ['completed', 'failed']);
    }

    /**
     * 检查任务是否正在处理
     */
    public function isProcessing()
    {
        return $this->status === 'processing';
    }

    /**
     * 获取成功率
     */
    public function getSuccessRate()
    {
        if ($this->processed_items == 0) {
            return 0;
        }
        
        return round(($this->successful_items / $this->processed_items) * 100, 2);
    }
}
