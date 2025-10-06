<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    


public function up()
{
    Schema::create('temas', function (Blueprint $table) {
            $table->id();
            $table->BigInteger('usuario_id')->unsigned();
            $table->string('titulo');
            $table->text('contenido');
            $table->string('imagen_url')->nullable();
            $table->timestamps();
        
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temas');
    }
};
