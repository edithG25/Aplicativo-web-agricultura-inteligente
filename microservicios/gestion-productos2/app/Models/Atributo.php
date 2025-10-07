<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Atributo extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
    ];

    // Relación muchos a muchos con Productos
    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'atributo_producto', 'atributo_id', 'producto_id')
                    ->withPivot('valor');
    }
}
