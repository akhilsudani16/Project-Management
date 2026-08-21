<?php

namespace Database\Seeders;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $project1 = Project::where('name', 'E-Commerce Platform')->first();
        $project2 = Project::where('name', 'Mobile App Development')->first();
        $project3 = Project::where('name', 'Data Analytics Dashboard')->first();

        $carol = User::where('email', 'carol@example.com')->first();
        $david = User::where('email', 'david@example.com')->first();
        $emma = User::where('email', 'emma@example.com')->first();
        $frank = User::where('email', 'frank@example.com')->first();
        $grace = User::where('email', 'grace@example.com')->first();

        // Tasks for Project 1: E-Commerce Platform
        Task::create([
            'project_id' => $project1->id,
            'user_id' => $emma->id,
            'created_by' => $carol->id,
            'title' => 'Design database schema for products and categories',
            'description' => 'Create a comprehensive database schema that supports products, categories, variants, and inventory tracking.',
            'status' => TaskStatus::DONE->value,
            'priority' => TaskPriority::HIGH->value,
            'due_date' => now()->subWeeks(8),
        ]);

        Task::create([
            'project_id' => $project1->id,
            'user_id' => $emma->id,
            'created_by' => $carol->id,
            'title' => 'Implement user authentication and authorization',
            'description' => 'Set up Laravel Sanctum for API authentication with role-based access control.',
            'status' => TaskStatus::DONE->value,
            'priority' => TaskPriority::HIGH->value,
            'due_date' => now()->subWeeks(6),
        ]);

        Task::create([
            'project_id' => $project1->id,
            'user_id' => $emma->id,
            'created_by' => $carol->id,
            'title' => 'Build product catalog API endpoints',
            'description' => 'Create REST API endpoints for managing products, categories, and inventory.',
            'status' => TaskStatus::IN_PROGRESS->value,
            'priority' => TaskPriority::HIGH->value,
            'due_date' => now()->addWeeks(1),
        ]);

        Task::create([
            'project_id' => $project1->id,
            'user_id' => $frank->id,
            'created_by' => $carol->id,
            'title' => 'Design homepage and product listing pages',
            'description' => 'Create modern, responsive designs for the main e-commerce pages with focus on user experience.',
            'status' => TaskStatus::IN_PROGRESS->value,
            'priority' => TaskPriority::MEDIUM->value,
            'due_date' => now()->addWeeks(2),
        ]);

        Task::create([
            'project_id' => $project1->id,
            'user_id' => $emma->id,
            'created_by' => $carol->id,
            'title' => 'Integrate payment gateway',
            'description' => 'Integrate Stripe payment processing with support for multiple payment methods.',
            'status' => TaskStatus::TODO->value,
            'priority' => TaskPriority::HIGH->value,
            'due_date' => now()->addWeeks(3),
        ]);

        Task::create([
            'project_id' => $project1->id,
            'user_id' => $emma->id, // Assigning to Emma instead of leaving unassigned
            'created_by' => $carol->id,
            'title' => 'Implement shopping cart functionality',
            'description' => 'Build shopping cart with session management and guest checkout support.',
            'status' => TaskStatus::TODO->value,
            'priority' => TaskPriority::MEDIUM->value,
            'due_date' => now()->addWeeks(4),
        ]);

        Task::create([
            'project_id' => $project1->id,
            'user_id' => $frank->id,
            'created_by' => $carol->id,
            'title' => 'Create admin dashboard interface',
            'description' => 'Design and implement admin dashboard for managing products, orders, and customers.',
            'status' => TaskStatus::TODO->value,
            'priority' => TaskPriority::LOW->value,
            'due_date' => now()->addWeeks(5),
        ]);

        // Tasks for Project 2: Mobile App Development
        Task::create([
            'project_id' => $project2->id,
            'user_id' => $emma->id,
            'created_by' => $carol->id,
            'title' => 'Set up React Native project structure',
            'description' => 'Initialize React Native project with proper folder structure and essential dependencies.',
            'status' => TaskStatus::DONE->value,
            'priority' => TaskPriority::URGENT->value,
            'due_date' => now()->subWeeks(4),
        ]);

        Task::create([
            'project_id' => $project2->id,
            'user_id' => $emma->id,
            'created_by' => $carol->id,
            'title' => 'Implement offline data synchronization',
            'description' => 'Build offline-first architecture with data sync when connection is available.',
            'status' => TaskStatus::IN_PROGRESS->value,
            'priority' => TaskPriority::HIGH->value,
            'due_date' => now()->addWeek(),
        ]);

        Task::create([
            'project_id' => $project2->id,
            'user_id' => $emma->id,
            'created_by' => $carol->id,
            'title' => 'Configure push notifications',
            'description' => 'Set up Firebase Cloud Messaging for push notifications on both iOS and Android.',
            'status' => TaskStatus::TODO->value,
            'priority' => TaskPriority::MEDIUM->value,
            'due_date' => now()->addWeeks(2),
        ]);

        // Tasks for Project 3: Data Analytics Dashboard
        Task::create([
            'project_id' => $project3->id,
            'user_id' => $grace->id,
            'created_by' => $david->id,
            'title' => 'Design data visualization components',
            'description' => 'Create reusable chart and graph components using Chart.js or D3.js.',
            'status' => TaskStatus::IN_PROGRESS->value,
            'priority' => TaskPriority::HIGH->value,
            'due_date' => now()->addDays(10),
        ]);

        Task::create([
            'project_id' => $project3->id,
            'user_id' => $grace->id,
            'created_by' => $david->id,
            'title' => 'Implement real-time data updates',
            'description' => 'Set up WebSocket connection for real-time dashboard updates.',
            'status' => TaskStatus::TODO->value,
            'priority' => TaskPriority::MEDIUM->value,
            'due_date' => now()->addWeeks(3),
        ]);

        Task::create([
            'project_id' => $project3->id,
            'user_id' => $grace->id, // Assigning to Grace instead of leaving unassigned
            'created_by' => $david->id,
            'title' => 'Create export functionality for reports',
            'description' => 'Allow users to export dashboard data to PDF, Excel, and CSV formats.',
            'status' => TaskStatus::TODO->value,
            'priority' => TaskPriority::LOW->value,
            'due_date' => now()->addWeeks(4),
        ]);

        Task::create([
            'project_id' => $project3->id,
            'user_id' => $grace->id,
            'created_by' => $david->id,
            'title' => 'Optimize database queries for large datasets',
            'description' => 'Improve query performance using indexes, caching, and query optimization techniques.',
            'status' => TaskStatus::IN_PROGRESS->value,
            'priority' => TaskPriority::URGENT->value,
            'due_date' => now()->addDays(5),
        ]);
    }
}
