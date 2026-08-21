<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActivityLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = User::where('email', 'superadmin@example.com')->first();
        $alice = User::where('email', 'alice@example.com')->first();
        $bob = User::where('email', 'bob@example.com')->first();
        $carol = User::where('email', 'carol@example.com')->first();
        $david = User::where('email', 'david@example.com')->first();
        $emma = User::where('email', 'emma@example.com')->first();
        $frank = User::where('email', 'frank@example.com')->first();
        $grace = User::where('email', 'grace@example.com')->first();

        $project1 = Project::where('name', 'E-Commerce Platform')->first();
        $project3 = Project::where('name', 'Data Analytics Dashboard')->first();

        $task1 = Task::where('title', 'Build product catalog API endpoints')->first();

        // User login activities
        ActivityLog::create([
            'user_id' => $superAdmin->id,
            'action' => 'user_login',
            'target_type' => 'User',
            'target_id' => $superAdmin->id,
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'created_at' => now()->subDays(30),
        ]);

        ActivityLog::create([
            'user_id' => $alice->id,
            'action' => 'user_login',
            'target_type' => 'User',
            'target_id' => $alice->id,
            'ip_address' => '192.168.1.101',
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
            'created_at' => now()->subDays(1),
        ]);

        ActivityLog::create([
            'user_id' => $emma->id,
            'action' => 'user_login',
            'target_type' => 'User',
            'target_id' => $emma->id,
            'ip_address' => '192.168.1.105',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'created_at' => now()->subHours(2),
        ]);

        // Project creation activities
        if ($project1) {
            ActivityLog::create([
                'user_id' => $alice->id,
                'action' => 'project_created',
                'target_type' => 'Project',
                'target_id' => $project1->id,
                'ip_address' => '192.168.1.101',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'created_at' => now()->subMonths(3),
            ]);

            ActivityLog::create([
                'user_id' => $alice->id,
                'action' => 'project_updated',
                'target_type' => 'Project',
                'target_id' => $project1->id,
                'ip_address' => '192.168.1.101',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'created_at' => now()->subWeeks(2),
            ]);
        }

        if ($project3) {
            ActivityLog::create([
                'user_id' => $bob->id,
                'action' => 'project_created',
                'target_type' => 'Project',
                'target_id' => $project3->id,
                'ip_address' => '192.168.1.102',
                'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
                'created_at' => now()->subMonths(4),
            ]);
        }

        // Task creation and updates
        if ($task1) {
            ActivityLog::create([
                'user_id' => $carol->id,
                'action' => 'task_created',
                'target_type' => 'Task',
                'target_id' => $task1->id,
                'ip_address' => '192.168.1.103',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => now()->subWeeks(3),
            ]);

            ActivityLog::create([
                'user_id' => $emma->id,
                'action' => 'task_updated',
                'target_type' => 'Task',
                'target_id' => $task1->id,
                'ip_address' => '192.168.1.105',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => now()->subDays(5),
            ]);

            ActivityLog::create([
                'user_id' => $emma->id,
                'action' => 'task_status_changed',
                'target_type' => 'Task',
                'target_id' => $task1->id,
                'ip_address' => '192.168.1.105',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => now()->subDays(2),
            ]);
        }

        // User invitation activities
        ActivityLog::create([
            'user_id' => $alice->id,
            'action' => 'user_invited',
            'target_type' => 'User',
            'target_id' => $carol->id,
            'ip_address' => '192.168.1.101',
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
            'created_at' => now()->subMonths(1),
        ]);

        ActivityLog::create([
            'user_id' => $carol->id,
            'action' => 'user_invited',
            'target_type' => 'User',
            'target_id' => $emma->id,
            'ip_address' => '192.168.1.103',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'created_at' => now()->subWeeks(3),
        ]);

        // Comment activities
        ActivityLog::create([
            'user_id' => $emma->id,
            'action' => 'comment_created',
            'target_type' => 'Task',
            'target_id' => $task1?->id,
            'ip_address' => '192.168.1.105',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'created_at' => now()->subDays(1),
        ]);

        // Attachment activities
        ActivityLog::create([
            'user_id' => $frank->id,
            'action' => 'attachment_uploaded',
            'target_type' => 'Project',
            'target_id' => $project1?->id,
            'ip_address' => '192.168.1.106',
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
            'created_at' => now()->subDays(3),
        ]);

        // Logout activities
        ActivityLog::create([
            'user_id' => $grace->id,
            'action' => 'user_logout',
            'target_type' => 'User',
            'target_id' => $grace->id,
            'ip_address' => '192.168.1.107',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'created_at' => now()->subHours(5),
        ]);

        // Failed login attempt (tracked against the unverified user)
        $henry = User::where('email', 'henry@example.com')->first();
        ActivityLog::create([
            'user_id' => $henry->id,
            'action' => 'login_failed',
            'target_type' => 'User',
            'target_id' => $henry->id,
            'ip_address' => '192.168.1.200',
            'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
            'created_at' => now()->subHours(12),
        ]);
    }
}
