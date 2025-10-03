<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PedidoItem extends Model
{
    protected $fillable = ['pedido_id', 'id_vendedor', 'producto_id', 'cantidad', 'precio_momento_pedido', 'producto_subtotal'];
    public function order()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }
}
