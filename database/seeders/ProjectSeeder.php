<?php

namespace Database\Seeders;

use App\Enums\ProjectStatus;
use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $org1 = Organization::where('name', 'Tech Innovations Inc.')->first();
        $org2 = Organization::where('name', 'Global Solutions Ltd.')->first();

        $alice = User::where('email', 'alice@example.com')->first();
        $bob = User::where('email', 'bob@example.com')->first();
        $carol = User::where('email', 'carol@example.com')->first();
        $david = User::where('email', 'david@example.com')->first();
        $emma = User::where('email', 'emma@example.com')->first();
        $frank = User::where('email', 'frank@example.com')->first();
        $grace = User::where('email', 'grace@example.com')->first();

        // Project 1: E-Commerce Platform (Org 1)
        $project1 = Project::create([
            'organization_id' => $org1->id,
            'name' => 'E-Commerce Platform',
            'description' => 'Building a modern e-commerce platform with advanced features including real-time inventory management, payment gateway integration, and customer analytics.',
            'status' => ProjectStatus::ACTIVE->value,
            'start_date' => now()->subMonths(3),
            'end_date' => now()->addMonths(3),
            'created_by' => $alice->id,
        ]);

        // Assign users to project1
        $project1->users()->attach($carol->id, [
            'id' => Str::uuid(),
            'assigned_by' => $alice->id,
            'assigned_at' => now()->subMonths(3),
            'invitation_token' => Str::random(32),
            'invited_by' => $alice->id,
            'invited_at' => now()->subMonths(3)->subDays(1),
            'accepted_at' => now()->subMonths(3),
        ]);

        $project1->users()->attach($emma->id, [
            'id' => Str::uuid(),
            'assigned_by' => $carol->id,
            'assigned_at' => now()->subMonths(2),
            'invitation_token' => Str::random(32),
            'invited_by' => $carol->id,
            'invited_at' => now()->subMonths(2)->subDays(1),
            'accepted_at' => now()->subMonths(2),
        ]);

        $project1->users()->attach($frank->id, [
            'id' => Str::uuid(),
            'assigned_by' => $carol->id,
            'assigned_at' => now()->subMonths(2),
            'invitation_token' => Str::random(32),
            'invited_by' => $carol->id,
            'invited_at' => now()->subMonths(2)->subDays(1),
            'accepted_at' => now()->subMonths(2),
        ]);

        // Project 2: Mobile App Development (Org 1)
        $project2 = Project::create([
            'organization_id' => $org1->id,
            'name' => 'Mobile App Development',
            'description' => 'Cross-platform mobile application for iOS and Android with offline capabilities and push notifications.',
            'status' => ProjectStatus::ACTIVE->value,
            'start_date' => now()->subMonths(2),
            'end_date' => now()->addMonths(4),
            'created_by' => $alice->id,
        ]);

        $project2->users()->attach($carol->id, [
            'id' => Str::uuid(),
            'assigned_by' => $alice->id,
            'assigned_at' => now()->subMonths(2),
            'invitation_token' => Str::random(32),
            'invited_by' => $alice->id,
            'invited_at' => now()->subMonths(2)->subDays(1),
            'accepted_at' => now()->subMonths(2),
        ]);

        $project2->users()->attach($emma->id, [
            'id' => Str::uuid(),
            'assigned_by' => $carol->id,
            'assigned_at' => now()->subMonth(),
            'invitation_token' => Str::random(32),
            'invited_by' => $carol->id,
            'invited_at' => now()->subMonth()->subDays(1),
            'accepted_at' => now()->subMonth(),
        ]);

        // Project 3: Data Analytics Dashboard (Org 2)
        $project3 = Project::create([
            'organization_id' => $org2->id,
            'name' => 'Data Analytics Dashboard',
            'description' => 'Interactive dashboard for visualizing business metrics and generating insights from large datasets.',
            'status' => ProjectStatus::ACTIVE->value,
            'start_date' => now()->subMonths(4),
            'end_date' => now()->addMonths(2),
            'created_by' => $bob->id,
        ]);

        $project3->users()->attach($david->id, [
            'id' => Str::uuid(),
            'assigned_by' => $bob->id,
            'assigned_at' => now()->subMonths(4),
            'invitation_token' => Str::random(32),
            'invited_by' => $bob->id,
            'invited_at' => now()->subMonths(4)->subDays(1),
            'accepted_at' => now()->subMonths(4),
        ]);

        $project3->users()->attach($grace->id, [
            'id' => Str::uuid(),
            'assigned_by' => $david->id,
            'assigned_at' => now()->subMonths(3),
            'invitation_token' => Str::random(32),
            'invited_by' => $david->id,
            'invited_at' => now()->subMonths(3)->subDays(1),
            'accepted_at' => now()->subMonths(3),
        ]);

        // Project 4: API Integration (Org 2 - On Hold)
        $project4 = Project::create([
            'organization_id' => $org2->id,
            'name' => 'Third-Party API Integration',
            'description' => 'Integrating multiple third-party APIs for enhanced functionality including payment processing, email services, and SMS notifications.',
            'status' => ProjectStatus::ON_HOLD->value,
            'start_date' => now()->subMonths(1),
            'end_date' => now()->addMonths(5),
            'created_by' => $bob->id,
        ]);

        $project4->users()->attach($david->id, [
            'id' => Str::uuid(),
            'assigned_by' => $bob->id,
            'assigned_at' => now()->subMonth(),
            'invitation_token' => Str::random(32),
            'invited_by' => $bob->id,
            'invited_at' => now()->subMonth()->subDays(1),
            'accepted_at' => now()->subMonth(),
        ]);

        // Project 5: Website Redesign (Org 1 - Draft)
        $project5 = Project::create([
            'organization_id' => $org1->id,
            'name' => 'Corporate Website Redesign',
            'description' => 'Complete redesign of the corporate website with modern UI/UX, improved performance, and SEO optimization.',
            'status' => ProjectStatus::DRAFT->value,
            'start_date' => null,
            'end_date' => null,
            'created_by' => $alice->id,
        ]);

        // Project 6: Legacy System Migration (Org 2 - Completed)
        $project6 = Project::create([
            'organization_id' => $org2->id,
            'name' => 'Legacy System Migration',
            'description' => 'Migrating from legacy monolithic system to modern microservices architecture.',
            'status' => ProjectStatus::COMPLETED->value,
            'start_date' => now()->subMonths(8),
            'end_date' => now()->subMonth(),
            'created_by' => $bob->id,
        ]);

        $project6->users()->attach($david->id, [
            'id' => Str::uuid(),
            'assigned_by' => $bob->id,
            'assigned_at' => now()->subMonths(8),
            'invitation_token' => Str::random(32),
            'invited_by' => $bob->id,
            'invited_at' => now()->subMonths(8)->subDays(1),
            'accepted_at' => now()->subMonths(8),
        ]);
    }
}
