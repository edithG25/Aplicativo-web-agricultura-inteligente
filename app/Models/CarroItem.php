<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CarroItem extends Model
{
    use HasFactory;
    protected $fillable = ['carro_id', 'id_vendedor', 'producto_id', 'cantidad', 'precio_en_carrito', 'producto_subtotal'];
    public function cart()
    {
        return $this->belongsTo(CarroCompra::class, 'carro_id');
    }
}
