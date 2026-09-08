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
        Schema::create('detalles_quiniela', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jugada_id')->constrained('jugadas')->cascadeOnDelete();
            $table->string('numero', 4);
            $table->tinyInteger('posicion')->unsigned();
            $table->enum('jurisdiccion', ['nacion', 'provincia', 'santa_fe', 'cordoba']);
            $table->decimal('importe', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalles_quiniela');
    }
};
