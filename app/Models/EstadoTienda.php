<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoTienda extends Model
{
    protected $table ='estados_tiendas';
    protected $fillable = [
        'estado'   
    ];
}
