<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role_id' => Role::query()->where('name', UserRole::MEMBER->value)->first()?->id,
            'status' => UserStatus::ACTIVE->value,
            'phone' => fake()->phoneNumber(),
            'job_title' => fake()->jobTitle(),
            'location' => fake()->city(),
            'avatar_path' => fake()->imageUrl(200, 200, 'people'),
            'bio' => fake()->sentence(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
            'status' => UserStatus::PENDING->value,
        ]);
    }

    /**
     * Super Admin role state.
     */
    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => Role::query()->where('name', UserRole::SUPER_ADMIN->value)->first()?->id,
            'job_title' => 'System Administrator',
            'bio' => 'Platform administrator with full system access.',
            'created_by' => User::query()->where('role_id', Role::query()->where('name', 'super_admin')->first()?->getKey())->inRandomOrder()->first()?->getKey(),
        ]);
    }

    /**
     * Organization Admin role state.
     */
    public function organizationAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => Role::query()->where('name', UserRole::ORGANIZATION_ADMIN->value)->first()?->id,
            'job_title' => 'Organization Administrator',
            'bio' => 'Managing organization operations and team members.',
            'created_by' => User::query()->where('role_id', Role::query()->where('name', 'super_admin')->first()?->getKey())->inRandomOrder()->first()?->getKey(),
        ]);
    }

    /**
     * Project Manager role state.
     */
    public function projectManager(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => Role::query()->where('name', UserRole::PROJECT_MANAGER->value)->first()?->id,
            'job_title' => 'Project Manager',
            'bio' => 'Delivering projects on time and within budget.',
            'created_by' => User::query()->where('role_id', Role::query()->where('name', 'organization_admin')->first()?->getKey())->inRandomOrder()->first()?->getKey(),
        ]);
    }

    /**
     * Member role state.
     */
    public function member(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => Role::query()->where('name', UserRole::MEMBER->value)->first()?->id,
            'job_title' => fake()->randomElement(['Software Developer', 'UI/UX Designer', 'QA Engineer', 'Business Analyst']),
            'bio' => fake()->sentence(10),
            'created_by' => User::query()->where('role_id', Role::query()->where('name', 'project_manager')->first()?->getKey())->inRandomOrder()->first()?->getKey(),
        ]);
    }

    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
            'deleted_by' => Role::query()->where('name', 'super_admin')->first()?->users()->first()?->getKey(),
        ]);
    }

    public function mustChangePassword(): static
    {
        return $this->state(fn (array $attributes) => [
            'password' => bcrypt('TempPassword123!'),
            'must_change_password' => true,
        ]);
    }
}
