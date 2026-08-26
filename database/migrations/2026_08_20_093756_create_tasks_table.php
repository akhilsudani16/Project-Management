<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained('projects');
            $table->foreignUuid('user_id')->nullable()->constrained('users');
            $table->foreignUuid('created_by')->constrained('users');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', TaskStatus::values())->default(TaskStatus::TODO->value);
            $table->enum('priority', TaskPriority::values())->default(TaskPriority::MEDIUM->value);
            $table->date('due_date')->nullable();
            $table->softDeletes();
            $table->foreignUuid('deleted_by')->nullable()->constrained('users');
            $table->timestampsTz();
        });
        DB::statement('CREATE INDEX idx_tasks_project_id ON tasks(project_id)');
        DB::statement('CREATE INDEX idx_tasks_user_id ON tasks(user_id)');
        DB::statement('CREATE INDEX idx_tasks_status ON tasks(status)');
        DB::statement('CREATE INDEX idx_tasks_priority ON tasks(priority)');
        DB::statement('CREATE INDEX idx_tasks_due_date ON tasks(project_id, status)');
        DB::statement('CREATE INDEX idx_tasks_deleted_at ON tasks(deleted_at)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
