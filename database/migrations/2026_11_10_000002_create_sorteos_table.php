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
        Schema::create('sorteos', function (Blueprint $table) {
            $table->id();
            $table->string('juego', 30); // quiniela, quini6, lotoplus, loto5, poceada
            $table->date('fecha_sorteo');
            $table->string('numero_sorteo', 50)->nullable();
            $table->string('jurisdiccion', 30)->nullable(); // quiniela
            $table->string('turno', 30)->nullable(); // quiniela
            $table->json('extracto');
            $table->json('tabla_premios')->nullable();
            $table->string('estado', 20)->default('pendiente'); // pendiente, escrutado, anulado
            $table->unsignedInteger('total_boletas_evaluadas')->default(0);
            $table->unsignedInteger('total_boletas_ganadoras')->default(0);
            $table->decimal('total_premios_liquidados', 14, 2)->default(0);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('escrutado_en')->nullable();
            $table->timestamps();

            $table->index(['juego', 'fecha_sorteo']);
            $table->index(['juego', 'jurisdiccion', 'turno', 'fecha_sorteo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sorteos');
    }
};
