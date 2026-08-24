<?php

use App\Enums\ProjectStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ProjectStatus::values())->default(ProjectStatus::DRAFT->value);
            $table->foreignUuid('organization_id')->constrained('organizations');
            $table->foreignUuid('created_by')->references('id')->on('users');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->softDeletes();
            $table->foreignUuid('deleted_by')->nullable()->constrained('users');
            $table->timestampsTz();
        });
        DB::statement('CREATE INDEX idx_projects_org_id ON projects(organization_id)');
        DB::statement('CREATE INDEX idx_projects_status ON projects(status)');
        DB::statement('CREATE INDEX idx_projects_deleted_at ON projects(deleted_at)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
