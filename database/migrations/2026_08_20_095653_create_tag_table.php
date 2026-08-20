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
        Schema::create('tag', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->Uuid('organization_id')->constrained('organizations');
            $table->string('name');
            $table->foreignUuid('deleted_by')->constrained('users');
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['organization_id', 'name']);
        });
        DB::statement('CREATE INDEX idx_tags_org_id ON tag(organization_id)');
        DB::statement('CREATE INDEX idx_tags_deleted_at ON tag(deleted_at)');
        }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tag');
    }
};
