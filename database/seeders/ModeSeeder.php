<?php

namespace Database\Seeders;

use App\Models\Mode;
use Illuminate\Database\Seeder;

class ModeSeeder extends Seeder
{
    public function run(): void
    {
        $modes = [
            'Solo' => 'Play alone in Singleplayer',
            'With friends' => 'Play with your friends in co-op offline or online',
            'Against other players' => 'Play against your friends or against people all around the world in multiplayer offline or online',
        ];

        foreach ($modes as $name => $description) {
            Mode::query()->updateOrCreate(
                ['name' => $name],
                ['description' => $description]
            );
        }
    }
}
