<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('metodos_pago', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // tarjeta crédito, PSE, PayPal, etc.
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('metodos_pago');
    }
};
