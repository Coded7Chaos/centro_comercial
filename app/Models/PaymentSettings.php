<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSettings extends Model
{
    protected $table = 'payment_settings';

    protected $fillable = [
        'banco_nombre',
        'banco_nro_cuenta',
        'banco_titular',
        'qr_imagen',
    ];
}
