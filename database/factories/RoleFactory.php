<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(UserRole::values()),
            'description' => fake()->sentence(),
        ];
    }

    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => UserRole::SUPER_ADMIN->value,
            'description' => 'Super Administrator with full system access',
        ]);
    }

    public function organizationAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => UserRole::ORGANIZATION_ADMIN->value,
            'description' => 'Organization Administrator',
        ]);
    }

    public function projectManager(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => UserRole::PROJECT_MANAGER->value,
            'description' => 'Project Manager',
        ]);
    }

    public function member(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => UserRole::MEMBER->value,
            'description' => 'Team Member',
        ]);
    }
}
