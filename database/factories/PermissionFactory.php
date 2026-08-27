<?php

namespace Database\Factories;

use App\Enums\Permission as PermissionEnum;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Permission>
 */
class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(PermissionEnum::values()),
        ];
    }

    /**
     * Force a specific permission instead of a random one.
     * Usage: Permission::factory()->named(PermissionEnum::CREATE_PROJECT)->create();
     */
    //    public function named(PermissionEnum $permission): static
    //    {
    //        return $this->state(fn (array $attributes) => [
    //            'name' => $permission->value,
    //        ]);
    //    }
}
