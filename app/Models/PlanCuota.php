<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanCuota extends Model
{
    use HasFactory;

    protected $table = 'plan_cuotas';

    protected $fillable = [
        'venta_id',
        'numero_cuota',
        'fecha_vencimiento',
        'monto_cuota',
        'saldo_pendiente',
        'recargo_mora',
        'total_a_pagar',
        'monto_pagado',
        'tipo_pago',
        'estado',
        'fecha_pago',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'fecha_pago'        => 'date',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }
}
