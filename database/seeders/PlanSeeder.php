<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'price_cents' => 0,
                'max_members' => 3,
                'max_projects' => 3,
                'description' => 'A small team finding its footing.',
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'price_cents' => 2900,
                'max_members' => 10,
                'max_projects' => 25,
                'description' => 'Room for a working team and a longer project list.',
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'price_cents' => 9900,
                'max_members' => null,
                'max_projects' => null,
                'description' => 'No member or project cap in this demo.',
            ],
        ];

        foreach ($plans as $plan) {
            Plan::query()->updateOrCreate(
                ['slug' => $plan['slug']],
                $plan,
            );
        }
    }
}
