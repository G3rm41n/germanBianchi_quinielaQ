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
        Schema::create('jugadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('modalidad', ['quiniela', 'quini6', 'lotoplus', 'loto5', 'poceada']);
            $table->decimal('monto_total', 10, 2);
            $table->decimal('precio_unitario_snapshot', 10, 2)->nullable();
            $table->enum('estado', ['pendiente', 'procesado', 'error_envio'])->default('pendiente');
            $table->uuid('idempotency_token')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jugadas');
    }
};
