<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DevolverPedido extends Model
{
    use HasFactory;
    protected $table = 'devolver_pedidos'; // Especifica el nombre de la tabla si el modelo no sigue la convención
    protected $fillable = ['pedido_id', 'usuario_id', 'razonDevolucion', 'estado', 'cantidadReembolso'];
    public function order()
    {
        return $this->belongsTo(Pedido::class);
    }
}
