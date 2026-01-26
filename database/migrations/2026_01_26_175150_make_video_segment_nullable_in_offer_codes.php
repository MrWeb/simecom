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
        Schema::table('offer_codes', function (Blueprint $table) {
            $table->string('video_segment')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('offer_codes', function (Blueprint $table) {
            $table->string('video_segment')->nullable(false)->change();
        });
    }
};
