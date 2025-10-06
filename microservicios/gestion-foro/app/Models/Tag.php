<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['nombre'];

    public function temas()
    {
        return $this->belongsToMany(Tema::class, 'tema_tag');
    }
}