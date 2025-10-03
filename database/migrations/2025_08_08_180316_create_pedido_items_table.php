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
        Schema::create('pedido_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->unsignedBigInteger('id_vendedor'); // ID del vendedor
            $table->unsignedBigInteger('producto_id'); // ID del producto
            $table->integer('cantidad');
            $table->decimal('precio_momento_pedido', 10, 2); // Precio del producto en el momento del pedido
            $table->decimal('producto_subtotal', 10, 2); // Precio del producto multiplicado por la cantidad
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido_items');
    }
};
