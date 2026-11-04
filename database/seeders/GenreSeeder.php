<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
    public function run(): void
    {
        $genres = [
            'action and adventure' => 'RPGs, Games with a complex world-building and fighting as a main mechanic (Assassins Creed, Skyrim, Tomb Raider)',
            'shooter' => 'First or third person shooters (Call of Duty, Half Life, Overwatch)',
            'sandbox' => "Open world games, survive, build or explore the map (Minecraft, Grand Theft Auto, Garry's mod)",
            'sports and motorsports' => 'Sports and races (fifa, need for speed, forza motorsport, NBA 2K)',
            'fighting' => 'Fighting 1vs1 games (Street Fighter, Tekken, Mortal Kombat)',
            'management and strategy' => 'Resource management games, base building and administration, real time strategy or turn based (Civilization, StarCraft, Cities: Skylines)',
            'horror' => 'Frightening games, survival-horrors, scary atmosphere (Dead by Daylight, Resident Evil, The Forest)',
            'puzzle' => 'Games that require the completion of puzzles to advance (Portal, The Witness, The Talos Principle)',
            'story based' => 'Games based on their narrative or that give a very important role to their story (Life is Strange, The Last of Us, Firewatch)',
            'arcade' => 'Old-school like, simple controls and mechanics, difficulty increases as you play (Undertale, The Binding of Isaac, Cuphead)',
            'simulator' => 'Games with realistic physics or driving, complex controls (Truck Simulator, Microsoft Flight Simulator, Kerbal Space Program)',
            'relaxing' => 'de-stressing games, quiet and chill, to have a good time (Stardew Valley, Abzu, Untitled goose game)',
            'party' => 'Fun and social games, are often a competition or a collection of minigames, casual games great for parties (Gang Beasts, Among Us, Fall Guys)',
        ];

        foreach ($genres as $name => $description) {
            Genre::query()->updateOrCreate(
                ['name' => $name],
                ['description' => $description]
            );
        }
    }
}
