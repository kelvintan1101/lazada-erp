<?php

namespace App\Jobs;

use App\Services\BulkUpdateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessBulkUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $taskId;

    /**
     * 任务最大尝试次数
     */
    public $tries = 3;

    /**
     * 任务超时时间（秒）
     */
    public $timeout = 3600; // 1小时

    /**
     * Create a new job instance.
     */
    public function __construct($taskId)
    {
        $this->taskId = $taskId;
    }

    /**
     * Execute the job.
     */
    public function handle(BulkUpdateService $bulkUpdateService): void
    {
        Log::info('开始处理批量更新任务', ['task_id' => $this->taskId]);

        try {
            $result = $bulkUpdateService->executeBulkUpdateTask($this->taskId);
            
            if ($result['success']) {
                Log::info('批量更新任务处理完成', [
                    'task_id' => $this->taskId,
                    'total' => $result['total'],
                    'successful' => $result['successful'],
                    'failed' => $result['failed']
                ]);
            } else {
                Log::error('批量更新任务处理失败', [
                    'task_id' => $this->taskId,
                    'message' => $result['message']
                ]);
            }
        } catch (\Exception $e) {
            Log::error('批量更新任务执行异常', [
                'task_id' => $this->taskId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e; // 重新抛出异常以触发重试机制
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('批量更新任务最终失败', [
            'task_id' => $this->taskId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        // 更新任务状态为失败
        try {
            $task = \App\Models\BulkUpdateTask::find($this->taskId);
            if ($task) {
                $task->update([
                    'status' => 'failed',
                    'completed_at' => now(),
                    'errors' => array_merge($task->errors ?? [], [
                        '任务执行失败: ' . $exception->getMessage()
                    ])
                ]);
            }
        } catch (\Exception $e) {
            Log::error('更新任务失败状态时出错', [
                'task_id' => $this->taskId,
                'error' => $e->getMessage()
            ]);
        }
    }
}
