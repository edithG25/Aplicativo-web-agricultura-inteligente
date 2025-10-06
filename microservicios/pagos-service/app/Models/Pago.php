<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model {
    protected $fillable = [
        'pedido_id', 'usuario_id', 'metodo_pago_id', 'monto', 'estado'
    ];

    public function metodo() {
        return $this->belongsTo(MetodoPago::class, 'metodo_pago_id');
    }
}