<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('skipped_imports', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
        });

        // Aggiorna il tipo errore per supportare 'missing_contact'
        // quando mancano sia email che telefono
    }

    public function down(): void
    {
        Schema::table('skipped_imports', function (Blueprint $table) {
            $table->dropColumn('phone');
        });
    }
};
