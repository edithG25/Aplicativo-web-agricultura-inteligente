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
        Schema::create('comentarios', function (Blueprint $table) {
            $table->id();
            $table->BigInteger('usuario_id')->unsigned();
            $table->unsignedBigInteger('tema_id');
            $table->text('contenido');
            $table->string('imagen_url')->nullable();
            $table->timestamps();
            $table->foreign('tema_id')->references('id')->on('temas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comentarios');
    }
};
