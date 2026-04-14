<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';

    protected $fillable = [
        'cliente_id',
        'tasa_interes_id',
        'total_bruto',
        'porcentaje_interes',
        'total_con_interes',
        'plazo_meses',
        'fecha_venta',
    ];

    protected $casts = [
        'fecha_venta' => 'date',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function tasaInteres()
    {
        return $this->belongsTo(TasaInteres::class, 'tasa_interes_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    public function planCuotas()
    {
        return $this->hasMany(PlanCuota::class, 'venta_id');
    }

    public function totalPagado(): float
    {
        return $this->planCuotas->sum('monto_pagado');
    }

    public function saldoPendiente(): float
    {
        return $this->total_con_interes - $this->totalPagado();
    }
}
