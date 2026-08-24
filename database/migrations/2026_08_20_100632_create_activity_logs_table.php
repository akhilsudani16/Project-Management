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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users');
            $table->string('action');
            $table->uuidMorphs('target');
            $table->ipAddress();
            $table->text('user_agent');
            $table->timestamps();
        });
        DB::statement('CREATE INDEX idx_activity_user_created ON activity_logs(user_id, created_at)');
        DB::statement('CREATE INDEX idx_activity_action ON activity_logs(action)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
