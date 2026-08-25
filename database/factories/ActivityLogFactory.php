<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'action' => fake()->randomElement([
                'login',
                'logout',
                'created_project',
                'updated_project',
                'deleted_project',
                'created_task',
                'updated_task',
                'completed_task',
                'commented',
                'uploaded_attachment',
            ]),
            'target_type' => fake()->randomElement(['Project', 'Task', 'User', 'Organization']),
            'target_id' => fake()->uuid(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
