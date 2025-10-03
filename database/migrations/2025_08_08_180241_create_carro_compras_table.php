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
        Schema::create('carro_compras', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id')->unique(); // ID del usuario, un carrito por usuario
            $table->string('estado')->default('activo'); // 'active', 'completed', 'abandoned'
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carro_compras');
    }
};
