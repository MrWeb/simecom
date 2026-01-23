<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('video_campaigns', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->enum('sms_status', ['pending', 'sent', 'failed'])->default('pending')->after('email_status');
            $table->timestamp('sms_sent_at')->nullable()->after('email_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('video_campaigns', function (Blueprint $table) {
            $table->dropColumn(['phone', 'sms_status', 'sms_sent_at']);
        });
    }
};
