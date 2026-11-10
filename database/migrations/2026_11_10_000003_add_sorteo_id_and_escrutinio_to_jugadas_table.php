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
        Schema::table('jugadas', function (Blueprint $table) {
            $table->foreignId('sorteo_id')->nullable()->after('monto_premio')->constrained('sorteos')->nullOnDelete();
            $table->json('detalle_escrutinio')->nullable()->after('sorteo_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jugadas', function (Blueprint $table) {
            $table->dropForeign(['sorteo_id']);
            $table->dropColumn(['sorteo_id', 'detalle_escrutinio']);
        });
    }
};
