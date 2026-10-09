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
        Schema::table('comunicaciones', function (Blueprint $table) {
            $table->foreignId('iglesia_id')->nullable()->after('nivel')->constrained('iglesias')->nullOnDelete();
            $table->foreignId('circuito_id')->nullable()->after('iglesia_id')->constrained('circuitos')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comunicaciones', function (Blueprint $table) {
            $table->dropConstrainedForeignId('iglesia_id');
            $table->dropConstrainedForeignId('circuito_id');
        });
    }
};
