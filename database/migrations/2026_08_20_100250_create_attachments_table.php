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
        Schema::create('attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users');
            $table->uuidMorphs('attachable');
            $table->string('path');
            $table->foreignUuid('deleted_by')->nullable()->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });
        DB::statement('CREATE INDEX idx_attachments_attachable ON attachments(attachable_type, attachable_id)');
        DB::statement('CREATE INDEX idx_attachments_user_id ON attachments(user_id)');
        DB::statement('CREATE INDEX idx_attachments_deleted_at ON attachments(deleted_at)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
