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
            $table->string('resultado', 20)->default('pendiente_sorteo')->after('estado');
            $table->decimal('monto_premio', 12, 2)->default(0)->after('resultado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jugadas', function (Blueprint $table) {
            $table->dropColumn(['resultado', 'monto_premio']);
        });
    }
};
