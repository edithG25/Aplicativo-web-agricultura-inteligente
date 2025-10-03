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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->decimal('total_monto', 10, 2);
            $table->string('estado')->default('pendiente'); // 'pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'
            $table->text('direccion_envio'); // Dirección de envío completa
            $table->string('nombre_recibe', 100); // Nombre de la persona que recibe el pedido
            $table->string('telefono', 10); // Teléfono de contacto
            $table->unsignedBigInteger('pago_id')->nullable(); // ID de la transacción del servicio de pagos
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
