<?php

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
        Schema::create('project_user', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained('projects');
            $table->foreignUuid('user_id')->constrained('users');
            $table->foreignUuid('assigned_by')->constrained('users');
            $table->timestamp('assigned_at')->nullable();
            $table->string('invitation_token')->unique();
            $table->foreignUuid('invited_by')->constrained('users');
            $table->timestampTz('invited_at')->nullable();
            $table->timestamp('invitation_expires_at')->nullable();
            $table->timestampTz('accepted_at')->nullable();
            $table->softDeletes();
            $table->foreignUuid('deleted_by')->nullable()->constrained('users');
            $table->timestampsTz();
        });
        DB::statement('CREATE INDEX idx_project_user_project_id ON project_user(project_id)');
        DB::statement('CREATE INDEX idx_project_user_user_id ON project_user(user_id)');
        DB::statement('CREATE INDEX idx_project_user_invitation_token ON project_user(invitation_token)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_user');
    }
};
