<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comentario extends Model
{
    protected $fillable = ['tema_id', 'usuario_id', 'contenido', 'imagen_url'];

    public function tema()
    {
        return $this->belongsTo(Tema::class);
    }

    public function citas()
    {
        return $this->hasMany(Cita::class);
    }
}
