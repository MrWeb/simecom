<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('offer_name')->nullable();
            $table->string('video_segment');  // es. offerta-1, offerta-2
            $table->enum('type', ['luce', 'gas'])->default('luce');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('code');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_codes');
    }
};
