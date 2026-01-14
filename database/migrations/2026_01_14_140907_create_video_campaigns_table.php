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
        Schema::create('video_campaigns', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('email');
            $table->string('customer_name');
            $table->json('video_combination'); // ["benvenuto", "offerta_3", "bolletta", ...]
            $table->string('video_hash', 32)->index(); // MD5 della combinazione per riuso
            $table->string('video_path')->nullable(); // path S3 video concatenato
            $table->enum('video_status', ['pending', 'processing', 'ready', 'failed'])->default('pending');
            $table->enum('email_status', ['pending', 'sent', 'failed'])->default('pending');
            $table->timestamp('email_sent_at')->nullable();
            $table->string('email_service_id')->nullable(); // ID da Mailgun/Sendgrid
            $table->timestamp('opened_at')->nullable(); // tracking apertura landing
            $table->timestamps();

            $table->index('email');
            $table->index('video_status');
            $table->index('email_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_campaigns');
    }
};
