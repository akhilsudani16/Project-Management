<?php

namespace Database\Factories;

use App\Enums\OrganizationStatus;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Organization>
 */
class OrganizationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'status' => OrganizationStatus::ACTIVE->value,
            'created_by' => Role::query()->where('name', 'super_admin')->first()?->users()->first()?->getKey(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrganizationStatus::ACTIVE->value,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrganizationStatus::INACTIVE->value,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrganizationStatus::ARCHIVED->value,
        ]);
    }
}
