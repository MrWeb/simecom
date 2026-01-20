<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('video_segments');
    }

    public function down(): void
    {
        Schema::create('video_segments', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('category');
            $table->string('file_path');
            $table->integer('duration_seconds')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->index('category');
        });
    }
};
