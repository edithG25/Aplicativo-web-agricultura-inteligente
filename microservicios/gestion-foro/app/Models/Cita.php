<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    protected $fillable = ['comentario_id', 'usuario_id', 'contenido', 'imagen_url'];

    public function comentario()
    {
        return $this->belongsTo(Comentario::class);
    }
}