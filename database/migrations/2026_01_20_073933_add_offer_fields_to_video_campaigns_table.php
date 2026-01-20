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
        Schema::table('video_campaigns', function (Blueprint $table) {
            $table->string('offer_code')->nullable()->after('video_type');
            $table->string('offer_name')->nullable()->after('offer_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('video_campaigns', function (Blueprint $table) {
            $table->dropColumn(['offer_code', 'offer_name']);
        });
    }
};
