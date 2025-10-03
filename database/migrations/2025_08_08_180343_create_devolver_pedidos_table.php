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
        Schema::create('devolver_pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->unsignedBigInteger('usuario_id'); // Usuario que solicita la devolución
            $table->text('razonDevolucion'); // Razón de la devolución
            $table->string('estado')->default('pendiente'); // 'pending', 'approved', 'rejected', 'completed'
            $table->decimal('cantidadReembolso', 10, 2)->nullable(); // Cantidad a reembolsar
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devolver_pedidos');
    }
};
