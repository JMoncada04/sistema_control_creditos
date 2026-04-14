<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TasaInteres extends Model
{
    use HasFactory;

    protected $table = 'tasa_interes';

    protected $fillable = [
        'plazo_meses',
        'porcentaje',
    ];

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'tasa_interes_id');
    }
}
