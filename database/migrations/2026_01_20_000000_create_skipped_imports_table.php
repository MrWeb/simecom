<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skipped_imports', function (Blueprint $table) {
            $table->id();
            $table->string('source_file');
            $table->unsignedInteger('row_number');
            $table->json('row_data');
            $table->string('error_type');  // 'missing_email' | 'missing_offer_code'
            $table->string('offer_code')->nullable();
            $table->string('email')->nullable();
            $table->string('customer_name')->nullable();
            $table->enum('status', ['pending', 'processed', 'ignored'])->default('pending');
            $table->foreignId('video_campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['source_file', 'status']);
            $table->index('error_type');
            $table->index('offer_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skipped_imports');
    }
};
