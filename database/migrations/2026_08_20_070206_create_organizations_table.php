<?php

use App\Enums\OrganizationStatus;
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
        Schema::create('organizations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->enum('status', OrganizationStatus::values())->default(OrganizationStatus::ACTIVE->value);
            $table->foreignUuid('created_by')->references('id')->on('users');
            $table->foreignUuid('deleted_by')->nullable()->references('id')->on('users');
            $table->softDeletes();
            $table->timestamps();
        });

        DB::statement('CREATE INDEX idx_organizations_status ON organizations(status)');
        DB::statement('CREATE INDEX idx_organizations_deleted_at ON organizations(deleted_at)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
