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
        Schema::create('comments', function (Blueprint $table) {
            $table->Uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users');
            $table->uuidMorphs('commentable');
            $table->text('body');
            $table->foreignUuid('deleted_by')->nullable()->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });

        DB::statement('CREATE INDEX idx_comments_commentable ON comments(commentable_type, commentable_id)');
        DB::statement('CREATE INDEX idx_comments_user_id ON comments(user_id)');
        DB::statement('CREATE INDEX idx_comments_deleted_at ON comments(deleted_at)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
