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
        Schema::table('users', function (Blueprint $table) {
            $table->date('fecha_nacimiento')->nullable()->after('role');
            $table->enum('genero', ['masculino', 'femenino', 'otro'])->nullable()->after('fecha_nacimiento');

            // Datos de vendedor
            $table->string('razon_social', 150)->nullable()->after('genero');
            $table->string('tipo_documento')->nullable()->after('razon_social');
            $table->string('numero_documento')->nullable()->after('tipo_documento');
            $table->string('telefono')->nullable()->after('numero_documento');
            $table->string('direccion')->nullable()->after('telefono');
            $table->string('numero_cuenta')->nullable()->after('direccion');
            $table->string('banco')->nullable()->after('numero_cuenta');
            $table->string('tipo_cuenta')->nullable()->after('banco');
            $table->string('nombre_tienda')->nullable()->after('tipo_cuenta');
            $table->string('categoria_productos')->nullable()->after('nombre_tienda');
            $table->text('politicas_envio')->nullable()->after('categoria_productos');

            // Archivos (solo guardamos la ruta)
            $table->string('documento_rut')->nullable()->after('politicas_envio');
            $table->string('documento_camcomercio')->nullable()->after('documento_rut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'fecha_nacimiento',
                'genero',
                'razon_social',
                'tipo_documento',
                'numero_documento',
                'telefono',
                'direccion',
                'numero_cuenta',
                'banco',
                'tipo_cuenta',
                'nombre_tienda',
                'categoria_productos',
                'politicas_envio',
                'documento_rut',
                'documento_camcomercio',
            ]);
        });
    }
};
