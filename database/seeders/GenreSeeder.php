<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
    public function run(): void
    {
        $genres = [
            'Action' => 'Fast-paced games focused on reflexes and combat.',
            'Adventure' => 'Exploration and story-driven experiences.',
            'RPG' => 'Character progression and role-playing.',
            'Strategy' => 'Planning and resource management.',
            'Simulation' => 'Realistic or systemic simulations.',
            'Puzzle' => 'Logic and problem solving.',
            'Platformer' => 'Jumping and precise movement.',
            'Shooter' => 'Ranged combat from first or third person.',
            'Sports' => 'Competitive sports and racing.',
            'Horror' => 'Tension, fear and survival.',
        ];

        foreach ($genres as $name => $description) {
            Genre::firstOrCreate(['name' => $name], ['description' => $description]);
        }
    }
}
