<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'stock',
        'imagen_url',
        'id_vendedor',
        'estado',
        'promocionado',
    ];

    // Relación muchos a muchos con Categorias
    public function categorias()
    {
        return $this->belongsToMany(Categoria::class, 'categoria_producto', 'producto_id', 'categoria_id');
    }

    // Relación muchos a muchos con Atributos (a través de la tabla pivote atributo_producto)
    public function atributos()
    {
        return $this->belongsToMany(Atributo::class, 'atributo_producto', 'producto_id', 'atributo_id')
                    ->withPivot('valor'); // Para acceder al campo 'valor' en la tabla pivote
    }

    // Relación uno a muchos con Reseñas
    public function resenias()
    {
        return $this->hasMany(Resenia::class, 'producto_id');
    }

    // // Método para calcular la calificación promedio (opcional, para conveniencia)
    // public function calificacionPromedio()
    // {
    //     return $this->reseñas()->avg('calificacion');
    // }
}
