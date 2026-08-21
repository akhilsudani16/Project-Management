<?php

use App\Enums\UserStatus;
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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->foreignUuid('role_id')->constrained('roles');
            $table->boolean('must_change_password')->default(false);
            $table->enum('status', UserStatus::values())->default(UserStatus::ACTIVE->value);
            $table->text('bio')->nullable();
            $table->string('phone');
            $table->string('job_title');
            $table->string('location');
            $table->string('avatar_path');
            $table->integer('failed_login_attempts')->default(0);
            $table->timestamp('lockout_until')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignUuid('created_by')->nullable()->constrained('users');
            $table->foreignUuid('deleted_by')->nullable()->constrained('users');
        });

        DB::statement('CREATE INDEX idx_users_email ON users(email) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX idx_users_status ON users(status) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX idx_users_role_id ON users(role_id) WHERE deleted_at IS NULL');

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
    }
};
