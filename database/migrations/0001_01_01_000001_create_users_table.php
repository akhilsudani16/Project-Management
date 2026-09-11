<?php

use App\Enums\UserStatus;
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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->foreignUuid('role_id')->constrained('roles');
            $table->boolean('must_change_password')->default(false);
            $table->enum('status', UserStatus::values())->default(UserStatus::ACTIVE->value);
            $table->text('bio')->nullable();
            $table->string('phone')->nullable();
            $table->string('job_title')->nullable();
            $table->string('location')->nullable();
            $table->string('avatar_path')->nullable();
            $table->integer('failed_login_attempts')->default(0);
            $table->timestamp('lockout_until')->nullable();
            $table->timestamp('email_verified_at')->nullable();

            $table->uuid('created_by')->nullable();
            $table->uuid('assigned_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->string('invitation_token')->nullable();
            $table->timestamp('invitation_accepted_at')->nullable();

            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
        });

        DB::statement('CREATE INDEX idx_users_email ON users(email) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX idx_users_status ON users(status) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX idx_users_role_id ON users(role_id) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX idx_users_invitation_token ON users(invitation_token) WHERE invitation_token IS NOT NULL');

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignUuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['deleted_by']);
        });
    }
};
