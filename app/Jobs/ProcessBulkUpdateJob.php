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
     * Maximum task retry attempts
     */
    public $tries = 3;

    /**
     * Task timeout (seconds)
     */
    public $timeout = 3600; // 1 hour

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
        Log::info('Starting bulk update task processing', ['task_id' => $this->taskId]);

        try {
            $result = $bulkUpdateService->executeBulkUpdateTask($this->taskId);
            
            if ($result['success']) {
                Log::info('Bulk update task processing completed', [
                    'task_id' => $this->taskId,
                    'total' => $result['total'],
                    'successful' => $result['successful'],
                    'failed' => $result['failed']
                ]);
            } else {
                Log::error('Bulk update task processing failed', [
                    'task_id' => $this->taskId,
                    'message' => $result['message']
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Bulk update task execution exception', [
                'task_id' => $this->taskId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e; // Re-throw exception to trigger retry mechanism
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Bulk update task finally failed', [
            'task_id' => $this->taskId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Update task status to failed
        try {
            $task = \App\Models\BulkUpdateTask::find($this->taskId);
            if ($task) {
                $task->update([
                    'status' => 'failed',
                    'completed_at' => now(),
                    'errors' => array_merge($task->errors ?? [], [
                        'Task execution failed: ' . $exception->getMessage()
                    ])
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error updating task failure status', [
                'task_id' => $this->taskId,
                'error' => $e->getMessage()
            ]);
        }
    }
}
