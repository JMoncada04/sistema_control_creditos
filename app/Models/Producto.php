<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'codigo',
        'nombre',
        'precio_venta',
        'stock_actual',
        'stock_minimo',
    ];

    public function detalleVentas()
    {
        return $this->hasMany(DetalleVenta::class, 'producto_id');
    }

    public function tieneStockBajo(): bool
    {
        return $this->stock_actual <= $this->stock_minimo;
    }
}
