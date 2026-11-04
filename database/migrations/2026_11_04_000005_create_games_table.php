<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('developer_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->unsignedSmallInteger('release_year')->nullable();
            $table->string('disclaimer')->nullable();
            $table->string('video_url')->nullable();
            $table->decimal('price', 8, 2)->nullable()->index();
            $table->string('buy_url')->nullable();
            $table->string('image_path')->nullable();
            $table->string('difficulty')->nullable();
            $table->string('audience')->nullable();
            $table->string('graphics')->nullable();
            $table->text('requirements')->nullable();
            $table->text('requirements_specs')->nullable();
            $table->unsignedInteger('game_length_minutes')->nullable();
            $table->decimal('score', 3, 1)->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
