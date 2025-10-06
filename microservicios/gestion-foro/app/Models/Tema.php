<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tema extends Model
{
    protected $fillable = ['titulo', 'contenido', 'usuario_id', 'imagen_url'];
    public function comentarios()
    {
        return $this->hasMany(Comentario::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'tema_tag');
    }
}
