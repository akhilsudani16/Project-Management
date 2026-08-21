<?php

namespace Database\Seeders;

use App\Enums\OrganizationStatus;
use App\Enums\OrganizationUserStatus;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrganizationSeeder extends Seeder
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

        // Organization 1: Tech Innovations Inc.
        $org1 = Organization::create([
            'name' => 'Tech Innovations Inc.',
            'status' => OrganizationStatus::ACTIVE->value,
            'created_by' => $superAdmin->id,
        ]);

        // Add members to org1
        $org1->users()->attach($alice->id, [
            'id' => Str::uuid(),
            'status' => OrganizationUserStatus::ACTIVE->value,
            'invited_by' => $superAdmin->id,
            'invited_at' => now()->subDays(30),
            'accepted_at' => now()->subDays(29),
        ]);

        $org1->users()->attach($carol->id, [
            'id' => Str::uuid(),
            'status' => OrganizationUserStatus::ACTIVE->value,
            'invited_by' => $alice->id,
            'invited_at' => now()->subDays(25),
            'accepted_at' => now()->subDays(24),
        ]);

        $org1->users()->attach($emma->id, [
            'id' => Str::uuid(),
            'status' => OrganizationUserStatus::ACTIVE->value,
            'invited_by' => $carol->id,
            'invited_at' => now()->subDays(20),
            'accepted_at' => now()->subDays(19),
        ]);

        $org1->users()->attach($frank->id, [
            'id' => Str::uuid(),
            'status' => OrganizationUserStatus::ACTIVE->value,
            'invited_by' => $carol->id,
            'invited_at' => now()->subDays(18),
            'accepted_at' => now()->subDays(17),
        ]);

        // Organization 2: Global Solutions Ltd.
        $org2 = Organization::create([
            'name' => 'Global Solutions Ltd.',
            'status' => OrganizationStatus::ACTIVE->value,
            'created_by' => $superAdmin->id,
        ]);

        // Add members to org2
        $org2->users()->attach($bob->id, [
            'id' => Str::uuid(),
            'status' => OrganizationUserStatus::ACTIVE->value,
            'invited_by' => $superAdmin->id,
            'invited_at' => now()->subDays(35),
            'accepted_at' => now()->subDays(34),
        ]);

        $org2->users()->attach($david->id, [
            'id' => Str::uuid(),
            'status' => OrganizationUserStatus::ACTIVE->value,
            'invited_by' => $bob->id,
            'invited_at' => now()->subDays(28),
            'accepted_at' => now()->subDays(27),
        ]);

        $org2->users()->attach($grace->id, [
            'id' => Str::uuid(),
            'status' => OrganizationUserStatus::ACTIVE->value,
            'invited_by' => $david->id,
            'invited_at' => now()->subDays(22),
            'accepted_at' => now()->subDays(21),
        ]);

        // Organization 3: Startup Ventures (Inactive)
        $org3 = Organization::create([
            'name' => 'Startup Ventures',
            'status' => OrganizationStatus::INACTIVE->value,
            'created_by' => $superAdmin->id,
        ]);

        $org3->users()->attach($alice->id, [
            'id' => Str::uuid(),
            'status' => OrganizationUserStatus::REJECTED->value,
            'invited_by' => $superAdmin->id,
            'invited_at' => now()->subDays(60),
            'accepted_at' => null,
        ]);

        // Pending invitation example
        $henry = User::where('email', 'henry@example.com')->first();
        $org1->users()->attach($henry->id, [
            'id' => Str::uuid(),
            'status' => OrganizationUserStatus::PENDING->value,
            'invited_by' => $alice->id,
            'invited_at' => now()->subDays(5),
            'accepted_at' => null,
        ]);
    }
}
