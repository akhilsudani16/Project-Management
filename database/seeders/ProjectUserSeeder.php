<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProjectUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = Project::with('organization.users')->get();

        foreach ($projects as $project) {
            // Get users from this project's organization
            $orgUsers = $project->organization->users;

            if ($orgUsers->isEmpty()) {
                continue;
            }

            // Assign 2-5 users per project
            $userCount = min(rand(2, 5), $orgUsers->count());
            $projectUsers = $orgUsers->random($userCount);

            foreach ($projectUsers as $user) {
                // Skip if already attached
                if ($project->users()->where('user_id', $user->id)->exists()) {
                    continue;
                }

                // Get assigner (someone from org, preferably not the user)
                $assignedBy = $orgUsers->where('id', '!=', $user->id)->isNotEmpty()
                    ? $orgUsers->where('id', '!=', $user->id)->random()->id
                    : $orgUsers->first()->id;

                // Same as inviter for simplicity
                $invitedBy = $assignedBy;

                // Attach user to project
                $project->users()->attach($user->id, [
                    'assigned_by' => $assignedBy,
                    'assigned_at' => now()->subDays(rand(1, 45)),
                    'invitation_token' => Str::random(32),
                    'invited_by' => $invitedBy,
                    'invited_at' => now()->subDays(rand(1, 50)),
                    'invitation_expires_at' => now()->addDays(7),
                    'accepted_at' => now()->subDays(rand(1, 40)),
                ]);
            }
        }
    }
}
