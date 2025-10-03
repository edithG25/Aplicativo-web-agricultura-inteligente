<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CarroCompra extends Model
{
    use HasFactory;
    protected $fillable = ['usuario_id', 'estado'];
    public function items()
    {
        return $this->hasMany(CarroItem::class, 'carro_id');
    }
}
