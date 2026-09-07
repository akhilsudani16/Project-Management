<?php

namespace Database\Seeders;

use App\Enums\OrganizationUserStatus;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrganizationUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organizations = Organization::all();
        $users = User::all();

        if ($organizations->isEmpty() || $users->isEmpty()) {
            return;
        }

        foreach ($organizations as $organization) {
            // Get random users (3-8 per organization)
            $userCount = min(rand(3, 8), $users->count());
            $orgUsers = $users->random($userCount);

            foreach ($orgUsers as $user) {
                // Skip if already attached
                if ($organization->users()->where('user_id', $user->id)->exists()) {
                    continue;
                }

                // Get inviter (someone different, or first user if same)
                $invitedBy = $users->where('id', '!=', $user->id)->isNotEmpty()
                    ? $users->where('id', '!=', $user->id)->random()->id
                    : $users->first()->id;

                // 80% active, 15% pending, 5% rejected
                $rand = rand(1, 100);
                if ($rand <= 80) {
                    $status = OrganizationUserStatus::ACTIVE->value;
                } elseif ($rand <= 95) {
                    $status = OrganizationUserStatus::PENDING->value;
                } else {
                    $status = OrganizationUserStatus::REJECTED->value;
                }

                // Attach user to organization
                $organization->users()->attach($user->id, [
                    'status' => $status,
                    'invited_by' => $invitedBy,
                    'invited_at' => now()->subDays(rand(1, 60)),
                    'accepted_at' => $status === OrganizationUserStatus::ACTIVE->value
                        ? now()->subDays(rand(1, 30))
                        : null,
                ]);
            }
        }
    }
}
