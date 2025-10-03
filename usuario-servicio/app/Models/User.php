<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable, HasFactory;

    protected $fillable = [
        'name', 
        'email', 
        'password', 
        'role',
        'fecha_nacimiento',
        'genero',
        'razon_social',
        'tipo_documento',
        'numero_documento',
        'telefono',
        'direccion',
        'numero_cuenta',
        'banco',
        'tipo_cuenta',
        'nombre_tienda',
        'categoria_productos',
        'politicas_envio',
        'documento_rut',
        'documento_camcomercio',
    ];
    protected $hidden   = ['password', 'remember_token', 'created_at', 'updated_at'];

    // Métodos requeridos por JWTSubject
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
