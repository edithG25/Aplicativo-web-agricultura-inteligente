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
        Schema::create('carro_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carro_id')->constrained('carro_compras')->onDelete('cascade'); // Relación con la tabla carts
            $table->unsignedBigInteger('id_vendedor'); // ID del vendedor
            $table->unsignedBigInteger('producto_id'); // ID del producto (viene del servicio de productos)
            $table->integer('cantidad')->default(1);
            $table->decimal('precio_en_carrito', 10, 2); // Precio del producto en el momento de añadirlo al carrito
            $table->decimal('producto_subtotal', 10, 2); // Precio del producto multiplicado por la cantidad
            $table->timestamps();
            $table->unique(['carro_id', 'producto_id']); // Un producto único por carrito
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carro_items');
    }
};
