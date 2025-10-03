<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pedido extends Model
{
    use HasFactory;
    protected $fillable = ['usuario_id', 'total_monto', 'estado', 'direccion_envio', 'nombre_recibe', 'telefono', 'pago_id'];
    public function items()
    {
        return $this->hasMany(PedidoItem::class);
    }
    public function returns()
    {
        return $this->hasMany(DevolverPedido::class); // Relación con devoluciones de pedidos
    }
}
