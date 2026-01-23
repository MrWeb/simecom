<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('video_segments', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->index(); // es. "offerta-sos-bee"
            $table->string('name'); // es. "SOS Bee"
            $table->enum('type', ['luce', 'gas']);
            $table->string('filename'); // nome del file video
            $table->boolean('is_offer')->default(true); // se è un segmento offerta selezionabile
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['slug', 'type']); // slug unico per tipo
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_segments');
    }
};
