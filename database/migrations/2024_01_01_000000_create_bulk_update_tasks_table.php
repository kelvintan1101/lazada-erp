<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bulk_update_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('product_title_update'); // 任务类型
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->string('file_path')->nullable(); // 上传的Excel文件路径
            $table->json('file_data')->nullable(); // 解析后的文件数据
            $table->integer('total_items')->default(0); // 总项目数
            $table->integer('processed_items')->default(0); // 已处理项目数
            $table->integer('successful_items')->default(0); // 成功项目数
            $table->integer('failed_items')->default(0); // 失败项目数
            $table->json('results')->nullable(); // 详细结果
            $table->json('errors')->nullable(); // 错误信息
            $table->timestamp('started_at')->nullable(); // 开始时间
            $table->timestamp('completed_at')->nullable(); // 完成时间
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_update_tasks');
    }
};
