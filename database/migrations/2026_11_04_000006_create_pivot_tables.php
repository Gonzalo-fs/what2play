<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_genre', function (Blueprint $table) {
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('genre_id')->constrained()->cascadeOnDelete();
            $table->primary(['game_id', 'genre_id']);
        });

        Schema::create('game_mode', function (Blueprint $table) {
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mode_id')->constrained()->cascadeOnDelete();
            $table->primary(['game_id', 'mode_id']);
        });

        Schema::create('game_user', function (Blueprint $table) {
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('completed')->default(false);
            $table->boolean('saved')->default(false);
            $table->timestamps();
            $table->primary(['game_id', 'user_id']);
        });

        Schema::create('user_genre_preferences', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('genre_id')->constrained()->cascadeOnDelete();
            $table->primary(['user_id', 'genre_id']);
        });

        Schema::create('user_mode_preferences', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mode_id')->constrained()->cascadeOnDelete();
            $table->primary(['user_id', 'mode_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_mode_preferences');
        Schema::dropIfExists('user_genre_preferences');
        Schema::dropIfExists('game_user');
        Schema::dropIfExists('game_mode');
        Schema::dropIfExists('game_genre');
    }
};
