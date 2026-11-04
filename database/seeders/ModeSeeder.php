<?php

namespace Database\Seeders;

use App\Models\Mode;
use Illuminate\Database\Seeder;

class ModeSeeder extends Seeder
{
    public function run(): void
    {
        $modes = [
            'Single player' => 'Played alone.',
            'Co-op' => 'Players cooperate towards a shared goal.',
            'Multiplayer' => 'Players compete against each other online.',
            'Local multiplayer' => 'Several players on the same device.',
        ];

        foreach ($modes as $name => $description) {
            Mode::firstOrCreate(['name' => $name], ['description' => $description]);
        }
    }
}
