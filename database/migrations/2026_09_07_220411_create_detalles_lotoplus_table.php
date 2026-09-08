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
        Schema::create('detalles_lotoplus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jugada_id')->constrained('jugadas')->cascadeOnDelete();
            $table->json('numeros');
            $table->tinyInteger('numero_plus')->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalles_lotoplus');
    }
};
