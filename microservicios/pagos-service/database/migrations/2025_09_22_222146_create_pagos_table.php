<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
    public function up() {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pedido_id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('metodo_pago_id');
            $table->decimal('monto', 10, 2);
            $table->string('estado')->default('pendiente'); // pendiente, aprobado, rechazado
            $table->timestamps();

            $table->foreign('metodo_pago_id')->references('id')->on('metodos_pago');
        });
    }

    public function down() {
        Schema::dropIfExists('pagos');
    }
};