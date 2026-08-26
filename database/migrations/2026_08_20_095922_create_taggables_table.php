<?php

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
        Schema::create('taggables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tag_id')->constrained('tag');
            $table->uuidMorphs('taggable');
            $table->timestamps();
        });
        DB::statement('CREATE INDEX idx_taggables_taggable ON taggables(taggable_type, taggable_id, tag_id)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taggables');
    }
};
