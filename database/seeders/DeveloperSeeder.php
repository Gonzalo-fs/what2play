<?php

namespace Database\Seeders;

use App\Models\Developer;
use Illuminate\Database\Seeder;

class DeveloperSeeder extends Seeder
{
    public function run(): void
    {
        $developers = [
            'Supergiant Games',
            'Black Matter',
            'Bend Studio',
            'Saber Interactive',
            'Frontier Developments',
            'CD PROJEKT RED',
            'Ubisoft',
        ];

        foreach ($developers as $name) {
            Developer::query()->firstOrCreate(['name' => $name]);
        }
    }
}
