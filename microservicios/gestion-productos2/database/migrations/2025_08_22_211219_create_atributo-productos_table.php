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
        Schema::create('atributo_producto', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('producto_id');
            $table->unsignedBigInteger('atributo_id');
            $table->string('valor'); // Ej: "kg", "Tomate chonto", "Sí"
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('cascade');
            $table->foreign('atributo_id')->references('id')->on('atributos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atributo-producto');
    }
};
