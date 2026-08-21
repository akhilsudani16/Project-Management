<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $org1 = Organization::where('name', 'Tech Innovations Inc.')->first();
        $org2 = Organization::where('name', 'Global Solutions Ltd.')->first();

        // Tags for Organization 1
        $tag1 = Tag::create([
            'organization_id' => $org1->id,
            'name' => 'frontend',
        ]);

        $tag2 = Tag::create([
            'organization_id' => $org1->id,
            'name' => 'backend',
        ]);

        $tag3 = Tag::create([
            'organization_id' => $org1->id,
            'name' => 'urgent',
        ]);

        $tag4 = Tag::create([
            'organization_id' => $org1->id,
            'name' => 'bug',
        ]);

        $tag5 = Tag::create([
            'organization_id' => $org1->id,
            'name' => 'feature',
        ]);

        $tag6 = Tag::create([
            'organization_id' => $org1->id,
            'name' => 'enhancement',
        ]);

        // Tags for Organization 2
        $tag7 = Tag::create([
            'organization_id' => $org2->id,
            'name' => 'data-analysis',
        ]);

        $tag8 = Tag::create([
            'organization_id' => $org2->id,
            'name' => 'api',
        ]);

        $tag9 = Tag::create([
            'organization_id' => $org2->id,
            'name' => 'performance',
        ]);

        $tag10 = Tag::create([
            'organization_id' => $org2->id,
            'name' => 'security',
        ]);

        // Attach tags to projects
        $project1 = Project::where('name', 'E-Commerce Platform')->first();
        $project2 = Project::where('name', 'Mobile App Development')->first();
        $project3 = Project::where('name', 'Data Analytics Dashboard')->first();

        if ($project1) {
            $project1->tags()->attach($tag2->id, ['id' => Str::uuid()]);
            $project1->tags()->attach($tag5->id, ['id' => Str::uuid()]);
        }

        if ($project2) {
            $project2->tags()->attach($tag1->id, ['id' => Str::uuid()]);
            $project2->tags()->attach($tag5->id, ['id' => Str::uuid()]);
        }

        if ($project3) {
            $project3->tags()->attach($tag7->id, ['id' => Str::uuid()]);
            $project3->tags()->attach($tag9->id, ['id' => Str::uuid()]);
        }

        // Attach tags to tasks
        $task1 = Task::where('title', 'Build product catalog API endpoints')->first();
        $task2 = Task::where('title', 'Design homepage and product listing pages')->first();
        $task3 = Task::where('title', 'Integrate payment gateway')->first();
        $task4 = Task::where('title', 'Optimize database queries for large datasets')->first();

        if ($task1) {
            $task1->tags()->attach($tag2->id, ['id' => Str::uuid()]);
            $task1->tags()->attach($tag5->id, ['id' => Str::uuid()]);
        }

        if ($task2) {
            $task2->tags()->attach($tag1->id, ['id' => Str::uuid()]);
            $task2->tags()->attach($tag6->id, ['id' => Str::uuid()]);
        }

        if ($task3) {
            $task3->tags()->attach($tag2->id, ['id' => Str::uuid()]);
            $task3->tags()->attach($tag3->id, ['id' => Str::uuid()]);
        }

        if ($task4) {
            $task4->tags()->attach($tag9->id, ['id' => Str::uuid()]);
            $task4->tags()->attach($tag3->id, ['id' => Str::uuid()]);
        }
    }
}
