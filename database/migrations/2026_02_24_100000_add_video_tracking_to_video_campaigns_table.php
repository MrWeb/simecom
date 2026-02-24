<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('video_campaigns', function (Blueprint $table) {
            $table->unsignedInteger('video_watched_seconds')->default(0)->after('opened_at');
            $table->unsignedInteger('video_duration')->nullable()->after('video_watched_seconds');
            $table->boolean('video_completed')->default(false)->after('video_duration');
        });
    }

    public function down(): void
    {
        Schema::table('video_campaigns', function (Blueprint $table) {
            $table->dropColumn(['video_watched_seconds', 'video_duration', 'video_completed']);
        });
    }
};
