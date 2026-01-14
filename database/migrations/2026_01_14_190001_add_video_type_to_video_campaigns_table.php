<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('video_campaigns', function (Blueprint $table) {
            $table->enum('video_type', ['luce', 'gas'])->default('luce')->after('video_combination');
        });
    }

    public function down(): void
    {
        Schema::table('video_campaigns', function (Blueprint $table) {
            $table->dropColumn('video_type');
        });
    }
};
