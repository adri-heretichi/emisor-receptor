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
    Schema::create('partida_ta_te_tis', function (Blueprint $table) {
        $table->id();
        $table->string('codigo_sala', 12)->unique();
        $table->string('jugador_x', 100);
        $table->string('jugador_o', 100)->nullable();
        $table->json('tablero');
        $table->string('turno', 1)->default('X');
        $table->string('estado', 20)->default('esperando');
        $table->string('ganador', 1)->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partida_ta_te_tis');
    }
};
