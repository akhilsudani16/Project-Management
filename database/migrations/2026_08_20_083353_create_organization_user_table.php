<?php

use App\Enums\OrganizationUserStatus;
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
        Schema::create('organization_user', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations');
            $table->foreignUuid('user_id')->constrained('users');
            $table->foreignUuid('invited_by')->constrained('users');
            $table->enum('status', OrganizationUserStatus::values())->default(OrganizationUserStatus::PENDING->value);
            $table->timestampTz('invited_at')->nullable();
            $table->timestampTz('accepted_at')->nullable();
            $table->foreignUuid('deleted_by')->nullable()->constrained('users');
            $table->softDeletes();
            $table->timestampsTz();
            $table->unique(['organization_id', 'user_id']);
        });
        DB::statement('CREATE INDEX idx_org_user_user_id ON organization_user(user_id) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX idx_org_user_org_id ON organization_user(organization_id) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX idx_org_user_status ON organization_user(status) WHERE deleted_at IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_user');
    }
};
