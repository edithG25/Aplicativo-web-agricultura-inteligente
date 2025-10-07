<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Resenia extends Model
{
    use HasFactory;
    protected $fillable = [
        'producto_id',
        'usuario_id',
        'calificacion',
        'comentario',
    ];
    // Relación muchos a uno con Producto
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}